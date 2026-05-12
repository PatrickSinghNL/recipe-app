<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class IngredientController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/ingredients/Index', [
            'ingredients' => Ingredient::with('stores')->orderBy('name', 'asc')->get(),
            'stores' => Store::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stores' => 'nullable|array',
            'stores.*.store_id' => 'required|exists:stores,id',
            'stores.*.price' => 'nullable|numeric',
            'stores.*.quantity' => 'nullable|string',
            'stores.*.product_url' => 'nullable|url|max:2048',
            'stores.*.image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,avif|max:2048',
        ]);

        $ingredient = Ingredient::create(['name' => $validated['name']]);

        $this->syncStores($ingredient, $request);

        return redirect()->back()->with('success', 'Ingredient added.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stores' => 'nullable|array',
            'stores.*.store_id' => 'required|exists:stores,id',
            'stores.*.price' => 'nullable|numeric',
            'stores.*.quantity' => 'nullable|string',
            'stores.*.product_url' => 'nullable|url|max:2048',
            'stores.*.image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,avif|max:2048',
        ]);

        $ingredient->update(['name' => $validated['name']]);

        $this->syncStores($ingredient, $request);

        return redirect()->back()->with('success', 'Ingredient updated.');
    }

    private function syncStores(Ingredient $ingredient, Request $request)
    {
        $storesData = $request->input('stores', []);
        $submittedStoreIds = [];

        foreach ($storesData as $index => $storeEntry) {
            $storeId = $storeEntry['store_id'];

            // Skip entries with no data at all
            $hasData = ! empty($storeEntry['price'])
                || ! empty($storeEntry['quantity'])
                || ! empty($storeEntry['product_url'])
                || $request->hasFile("stores.{$index}.image");

            if (! $hasData) {
                continue;
            }

            $submittedStoreIds[] = $storeId;

            $pivotData = [
                'price' => $storeEntry['price'] ?? null,
                'quantity' => $storeEntry['quantity'] ?? null,
                'product_url' => $this->cleanUrl($storeEntry['product_url'] ?? null),
            ];

            // Handle image upload
            if ($request->hasFile("stores.{$index}.image")) {
                // Delete old image if exists
                /** @var \App\Models\Store|null $existingPivot */
                $existingPivot = $ingredient->stores()->where('store_id', $storeId)->first();
                if ($existingPivot && $existingPivot->pivot->image) {
                    Storage::disk('public')->delete($existingPivot->pivot->image);
                }
                $pivotData['image'] = $request->file("stores.{$index}.image")->store('ingredients', 'public');
            }

            // Check if pivot already exists
            $existing = $ingredient->stores()->where('store_id', $storeId)->first();
            if ($existing) {
                $ingredient->stores()->updateExistingPivot($storeId, $pivotData);
            } else {
                $ingredient->stores()->attach($storeId, $pivotData);
            }
        }

        // Remove stores that were cleared (had data before, now empty)
        $storesToDetach = $ingredient->stores()
            ->whereNotIn('stores.id', $submittedStoreIds)
            ->get();

        foreach ($storesToDetach as $store) {
            /** @var \App\Models\Store $store */
            $pivot = $store->pivot;
            if ($pivot->image) {
                Storage::disk('public')->delete($pivot->image);
            }
        }

        $ingredient->stores()->detach($storesToDetach->pluck('id'));
    }

    public function destroy(Ingredient $ingredient)
    {
        // Clean up all pivot images
        foreach ($ingredient->stores as $store) {
            /** @var \App\Models\Store $store */
            $pivot = $store->pivot;
            if ($pivot->image) {
                Storage::disk('public')->delete($pivot->image);
            }
        }

        $ingredient->delete();

        return redirect()->back()->with('success', 'Ingredient deleted.');
    }

    public function updatePrices()
    {
        $pivotRows = DB::table('ingredient_store')
            ->whereNotNull('product_url')
            ->get();

        $updatedCount = 0;
        $errors = [];

        foreach ($pivotRows as $row) {
            $data = $this->getScrapedData($row->product_url, false);
            if (isset($data['price'])) {
                DB::table('ingredient_store')
                    ->where('id', $row->id)
                    ->update(['price' => $data['price'], 'updated_at' => now()]);
                $updatedCount++;
            } elseif (isset($data['error'])) {
                $ingredientName = DB::table('ingredients')->where('id', $row->ingredient_id)->value('name');
                $storeName = DB::table('stores')->where('id', $row->store_id)->value('name');
                $errors[] = "Error updating {$ingredientName} ({$storeName}): {$data['error']}";
            }
        }

        return response()->json([
            'message' => "Successfully updated prices for $updatedCount entries.",
            'updated_count' => $updatedCount,
            'errors' => $errors,
        ]);
    }

    public function scrapeProduct(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $data = $this->getScrapedData($request->url);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], $data['status'] ?? 422);
        }

        return response()->json($data);
    }

    private function getScrapedData($url, $includeImage = true)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://www.ah.nl/',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Ch-Ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                'Sec-Ch-Ua-Mobile' => '?0',
                'Sec-Ch-Ua-Platform' => '"macOS"',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
                'Sec-Fetch-User' => '?1',
                'DNT' => '1',
            ])->timeout(25)->get($url);

            $price = null;
            $image = null;
            $name = null;
            $quantity = null;
            $storeName = null;
            $html = null;

            if (! $response->successful()) {
                // FALLBACK 1: For AH.nl, try their internal API
                if (str_contains($url, 'ah.nl')) {
                    $path = parse_url($url, PHP_URL_PATH);
                    if ($path) {
                        $apiUrl = 'https://www.ah.nl/service/rest/delegate?url='.urlencode($path);
                        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15';

                        $apiResponse = Http::withHeaders([
                            'User-Agent' => $userAgent,
                            'Accept' => 'application/json',
                            'Accept-Language' => 'nl-NL,nl;q=0.9',
                        ])->timeout(20)->get($apiUrl);

                        if ($apiResponse->successful()) {
                            $data = $apiResponse->json();
                            $productData = null;
                            if (isset($data['_embedded']['lanes'])) {
                                foreach ($data['_embedded']['lanes'] as $lane) {
                                    if (isset($lane['_embedded']['items'])) {
                                        foreach ($lane['_embedded']['items'] as $item) {
                                            if (($item['type'] ?? '') === 'PRODUCT_DETAILS') {
                                                $productData = $item['_embedded']['product'] ?? null;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($productData) {
                                $name = $productData['title'] ?? null;
                                $price = $productData['price']['current'] ?? null;
                                if (isset($productData['images'])) {
                                    $img = end($productData['images']);
                                    $image = $img['url'] ?? null;
                                }

                                return $this->finalizeData($price, $image, $name, $quantity, $storeName, $url, $includeImage);
                            }
                        }
                    }

                    // Try direct REST API as a secondary AH fallback with system curl
                    if (preg_match('/product\/([a-z0-9]+)/i', $url, $matches)) {
                        $productId = $matches[1];
                        $restUrl = "https://www.ah.nl/service/rest/products/$productId";
                        $tempFile = tempnam(sys_get_temp_dir(), 'ah_rest_');

                        $command = '/usr/bin/curl -s -L --http2 '.
                                   '-A "App-AH/8.60.1 (iPhone14,2; iOS 17.1.1)" '.
                                   '-H "X-Application: AH-App" '.
                                   '-H "Accept: application/json" '.
                                   '-o '.escapeshellarg($tempFile).' '.escapeshellarg($restUrl);

                        exec($command);
                        $json = file_get_contents($tempFile);
                        @unlink($tempFile);

                        $productData = json_decode($json, true);
                        if ($productData && isset($productData['title'])) {
                            $name = $productData['title'] ?? null;
                            $price = $productData['price']['current'] ?? null;
                            if (isset($productData['images'])) {
                                $img = end($productData['images']);
                                $image = $img['url'] ?? null;
                            }

                            return $this->finalizeData($price, $image, $name, $quantity, $storeName, $url, $includeImage);
                        }
                    }
                }

                // FALLBACK 2: Try PHP native stream
                if ($response->status() === 403) {
                    $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15';
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => "User-Agent: $userAgent\r\n".
                                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8\r\n".
                                        "Accept-Language: nl-NL,nl;q=0.9\r\n".
                                        "Connection: close\r\n",
                            'timeout' => 15,
                            'follow_location' => 1,
                            'ignore_errors' => true,
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]);

                    $html = @file_get_contents($url, false, $context);

                    if (! ($html && strlen($html) > 1000 && ! str_contains($html, 'Access Denied'))) {
                        return ['error' => 'Could not access the website (Backend blocked).', 'status' => 422];
                    }
                } else {
                    return ['error' => 'Could not access the website (Status: '.$response->status().').', 'status' => 422];
                }
            } else {
                $html = $response->body();
            }

            // Detect store from URL
            if (str_contains($url, 'jumbo.com')) {
                $storeName = 'Jumbo';
                if (preg_match('/data-testid="product-title".*?>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }
                if (preg_match('/data-testid="product-subtitle".*?>(.*?)<\/span>/is', $html, $matches)) {
                    $quantity = trim(strip_tags($matches[1]));
                }
            } elseif (str_contains($url, 'ah.nl')) {
                $storeName = 'AH';
                if (preg_match('/data-testid="pdp-hero-product-title".*?<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                } elseif (preg_match('/class="[^"]*product-hero-title_title[^"]*".*?>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }

                if (preg_match('/class="[^"]*product-hero-title_unitInfo[^"]*".*?class="[^"]*product-hero-title_unitInfoContent[^"]*".*?>(.*?)<\/span>/is', $html, $matches)) {
                    $quantity = trim(strip_tags($matches[1]));
                }
            } elseif (str_contains($url, 'vomar.nl')) {
                $storeName = 'Vomar';
                if (preg_match('/<h1[^>]*>\s*(.*?)\s*<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }
                if (preg_match('/<span[^>]*class="large"[^>]*>(\d+)\.<\/span>.*?<span[^>]*class="small"[^>]*>(\d+)<\/span>/is', $html, $matches)) {
                    $price = $matches[1].'.'.$matches[2];
                }
                if (preg_match('/<div[^>]*class="image"[^>]*>.*?<img[^>]*src="([^"]+)"/is', $html, $matches)) {
                    $image = $matches[1];
                }
            } elseif (str_contains($url, 'lidl.nl')) {
                $storeName = 'Lidl';
                if (preg_match('/<h1[^>]*class="heading__title"[^>]*>\s*(.*?)\s*<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }
                if (preg_match('/<div[^>]*class="ods-price__value"[^>]*>\s*([0-9.]+)\s*<\/div>/is', $html, $matches)) {
                    $price = $matches[1];
                }
                if (preg_match('/<div[^>]*class="ods-price__footer"[^>]*>.*?<span[^>]*>(.*?)<br/is', $html, $matches)) {
                    $quantity = trim(strip_tags($matches[1]));
                }
                if (preg_match('/<img[^>]*class="[^"]*media-carousel-item__item[^"]*"[^>]*src="([^"]+)"/is', $html, $matches)) {
                    $image = $matches[1];
                }
            }

            // JSON-LD Fallback
            if (preg_match_all('/<script.*?type="application\/ld\+json".*?>(.*?)<\/script>/is', $html, $scriptMatches)) {
                foreach ($scriptMatches[1] as $jsonContent) {
                    $data = json_decode($jsonContent, true);
                    if (! $data) {
                        continue;
                    }
                    $jsonItems = isset($data['@graph']) ? $data['@graph'] : [$data];
                    foreach ($jsonItems as $item) {
                        $type = $item['@type'] ?? '';
                        if (is_array($type)) {
                            $type = implode(' ', $type);
                        }
                        if (str_contains($type, 'Product')) {
                            if (isset($item['offers']) && ! $price) {
                                $offers = is_array($item['offers']) && ! isset($item['offers']['price']) && isset($item['offers'][0]) ? $item['offers'][0] : $item['offers'];
                                if (isset($offers['price'])) {
                                    $price = $offers['price'];
                                }
                            }
                            if (isset($item['image']) && ! $image) {
                                $imgData = is_array($item['image']) ? $item['image'][0] : $item['image'];
                                $image = (is_array($imgData) && isset($imgData['url'])) ? $imgData['url'] : $imgData;
                            }
                            if (isset($item['name']) && ! $name) {
                                $name = $item['name'];
                            }
                        }
                    }
                }
            }

            // Meta Tags Fallback
            if (! $price) {
                $metaPatterns = ['/<meta.*?property="og:price:amount".*?content="(.*?)".*?>/i', '/<meta.*?property="product:price:amount".*?content="(.*?)".*?>/i', '/<meta.*?itemprop="price".*?content="(.*?)".*?>/i'];
                foreach ($metaPatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $price = $matches[1];
                        break;
                    }
                }
            }
            if (! $image) {
                $imagePatterns = ['/<meta.*?property="og:image".*?content="(.*?)".*?>/i', '/<meta.*?property="product:image".*?content="(.*?)".*?>/i', '/<meta.*?itemprop="image".*?content="(.*?)".*?>/i'];
                foreach ($imagePatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $image = $matches[1];
                        break;
                    }
                }
            }
            if (! $name) {
                $namePatterns = ['/<meta.*?property="og:title".*?content="(.*?)".*?>/i', '/<meta.*?name="twitter:title".*?content="(.*?)".*?>/i', '/<meta.*?itemprop="name".*?content="(.*?)".*?>/i'];
                foreach ($namePatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $name = $matches[1];
                        break;
                    }
                }
            }

            // HTML Fallbacks
            if (! $price) {
                if (preg_match('/class="screenreader-only".*?Prijs:.*?([0-9,.]+)/is', $html, $matches)) {
                    $price = str_replace(',', '.', $matches[1]);
                } elseif (preg_match('/class="whole".*?>(\d+).*?class="fractional".*?>(\d+)/is', $html, $matches)) {
                    $price = $matches[1].'.'.$matches[2];
                } elseif (preg_match('/aria-label="voor (\d+) euro en (\d+) cent"/is', $html, $matches)) {
                    $price = $matches[1].'.'.$matches[2];
                } elseif (preg_match('/data-testid="pdp-hero-price".*?><p>(\d+)<\/p>.*?current-price_cents.*?">(\d+)<\/sup>/is', $html, $matches)) {
                    $price = $matches[1].'.'.$matches[2];
                }
            }
            if (! $image && preg_match('/<img[^>]+data-testid="jum-product-image"[^>]+src="([^"]+)"/is', $html, $matches)) {
                $image = $matches[1];
            }

            return $this->finalizeData($price, $image, $name, $quantity, $storeName, $url, $includeImage);

        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'status' => 500];
        }
    }

    private function finalizeData($price, $image, $name, $quantity, $storeName, $url, $includeImage)
    {
        if ($image && preg_match('/\.(html|php|aspx|jsp)($|\?)/i', $image)) {
            $image = null;
        }
        if ($price) {
            $price = preg_replace('/[^0-9.]/', '', $price);
        }

        $base64Image = null;
        if ($includeImage && $image) {
            if (str_starts_with($image, '/')) {
                $parsedUrl = parse_url($url);
                $image = ($parsedUrl['scheme'] ?? 'https').'://'.($parsedUrl['host'] ?? '').$image;
            }
            try {
                $imageResponse = Http::timeout(10)->withHeaders(['User-Agent' => 'Mozilla/5.0...'])->get($image);
                if ($imageResponse->successful() && str_starts_with($imageResponse->header('Content-Type'), 'image/')) {
                    $base64Image = 'data:'.$imageResponse->header('Content-Type').';base64,'.base64_encode($imageResponse->body());
                }
            } catch (\Exception $e) {
            }
        }

        return [
            'price' => $price ? (float) $price : null,
            'image' => $image,
            'image_base64' => $base64Image,
            'name' => $name ? html_entity_decode($name) : null,
            'quantity' => $quantity,
            'store_name' => $storeName,
        ];
    }

    private function cleanUrl($url)
    {
        if (! $url) {
            return $url;
        }

        $parsed = parse_url($url);
        if (! $parsed) {
            return $url;
        }

        // Strip fragment (the # part)
        $cleanUrl = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').($parsed['path'] ?? '');

        // For Lidl, we specifically want to strip the tracking fragment
        // For other sites, we keep the query string as it might be needed
        if (isset($parsed['query'])) {
            $cleanUrl .= '?'.$parsed['query'];
        }

        return $cleanUrl;
    }
}
