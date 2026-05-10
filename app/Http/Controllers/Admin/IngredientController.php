<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class IngredientController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/ingredients/Index', [
            'ingredients' => Ingredient::with('store')->orderBy('name', 'asc')->get(),
            'stores' => Store::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string',
            'price' => 'nullable|numeric',
            'product_url' => 'nullable|url|max:2048',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,avif|max:2048',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ingredients', 'public');
        }

        Ingredient::create($validated);

        return redirect()->back()->with('success', 'Ingredient added.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string',
            'price' => 'nullable|numeric',
            'product_url' => 'nullable|url|max:2048',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,avif|max:2048',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        if ($request->hasFile('image')) {
            if ($ingredient->image) {
                Storage::disk('public')->delete($ingredient->image);
            }
            $validated['image'] = $request->file('image')->store('ingredients', 'public');
        } else {
            unset($validated['image']);
        }

        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient updated.');
    }

    public function destroy(Ingredient $ingredient)
    {
        if ($ingredient->image) {
            Storage::disk('public')->delete($ingredient->image);
        }
        $ingredient->delete();

        return redirect()->back()->with('success', 'Ingredient deleted.');
    }

    public function scrapeProduct(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

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
            ])->timeout(25)->get($request->url);

            if (! $response->successful()) {
                // FALLBACK 1: For AH.nl, try their internal API
                if (str_contains($request->url, 'ah.nl')) {
                    $path = parse_url($request->url, PHP_URL_PATH);
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
                                $image = null;
                                if (isset($productData['images'])) {
                                    $img = end($productData['images']);
                                    $image = $img['url'] ?? null;
                                }
                                goto process_image;
                            }
                        }
                    }

                    // Try direct REST API as a secondary AH fallback with system curl
                    if (preg_match('/product\/([a-z0-9]+)/i', $request->url, $matches)) {
                        $productId = $matches[1];
                        $restUrl = "https://www.ah.nl/service/rest/products/$productId";
                        $tempFile = tempnam(sys_get_temp_dir(), 'ah_rest_');

                        // Use system curl with mobile app headers
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
                            goto process_image;
                        }
                    }
                }

                // FALLBACK 2: Try PHP native stream as a last resort (different TCP fingerprint than curl)
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

                    $html = @file_get_contents($request->url, false, $context);

                    if ($html && strlen($html) > 1000 && ! str_contains($html, 'Access Denied')) {
                        goto parse_html;
                    }

                    if (! $html) {
                        return response()->json(['error' => 'Could not access the website (Backend blocked).'], 422);
                    }

                    return response()->json(['error' => 'Could not access the website (Backend blocked). Info: '.substr(strip_tags($html), 0, 100)], 422);
                }

                return response()->json(['error' => 'Could not access the website (Status: '.$response->status().').'], 422);
            }

            $html = $response->body();
            parse_html:
            $price = null;
            $image = null;
            $name = null;
            $quantity = null;
            $storeName = null;

            // Detect store from URL
            if (str_contains($request->url, 'jumbo.com')) {
                $storeName = 'Jumbo';
                if (preg_match('/data-testid="product-title".*?>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }
                if (preg_match('/data-testid="product-subtitle".*?>(.*?)<\/span>/is', $html, $matches)) {
                    $quantity = trim(strip_tags($matches[1]));
                }
            } elseif (str_contains($request->url, 'ah.nl')) {
                $storeName = 'AH';
                if (preg_match('/data-testid="pdp-hero-product-title".*?<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                } elseif (preg_match('/class="[^"]*product-hero-title_title[^"]*".*?>(.*?)<\/h1>/is', $html, $matches)) {
                    $name = trim(strip_tags($matches[1]));
                }

                if (preg_match('/class="[^"]*product-hero-title_unitInfo[^"]*".*?class="[^"]*product-hero-title_unitInfoContent[^"]*".*?>(.*?)<\/span>/is', $html, $matches)) {
                    $quantity = trim(strip_tags($matches[1]));
                }
            }

            // 1. Try JSON-LD (Usually most reliable)
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
                            // Extract price
                            if (isset($item['offers'])) {
                                $offers = is_array($item['offers']) && ! isset($item['offers']['price']) && isset($item['offers'][0])
                                    ? $item['offers'][0]
                                    : $item['offers'];

                                if (isset($offers['price'])) {
                                    $price = $offers['price'];
                                }
                            }
                            // Extract image
                            if (isset($item['image']) && ! $image) {
                                $imgData = is_array($item['image']) ? $item['image'][0] : $item['image'];
                                $image = (is_array($imgData) && isset($imgData['url'])) ? $imgData['url'] : $imgData;
                            }
                            // Extract name
                            if (isset($item['name']) && ! $name) {
                                $name = $item['name'];
                            }
                        }
                    }
                }
            }

            // 2. Try Meta Tags (Fallback for price/image/name)
            if (! $price) {
                $metaPatterns = [
                    '/<meta.*?property="og:price:amount".*?content="(.*?)".*?>/i',
                    '/<meta.*?property="product:price:amount".*?content="(.*?)".*?>/i',
                    '/<meta.*?itemprop="price".*?content="(.*?)".*?>/i',
                ];
                foreach ($metaPatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $price = $matches[1];
                        break;
                    }
                }
            }

            if (! $image) {
                $imagePatterns = [
                    '/<meta.*?property="og:image".*?content="(.*?)".*?>/i',
                    '/<meta.*?property="product:image".*?content="(.*?)".*?>/i',
                    '/<meta.*?itemprop="image".*?content="(.*?)".*?>/i',
                ];
                foreach ($imagePatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $image = $matches[1];
                        break;
                    }
                }
            }

            if (! $name) {
                $namePatterns = [
                    '/<meta.*?property="og:title".*?content="(.*?)".*?>/i',
                    '/<meta.*?name="twitter:title".*?content="(.*?)".*?>/i',
                    '/<meta.*?itemprop="name".*?content="(.*?)".*?>/i',
                ];
                foreach ($namePatterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) {
                        $name = $matches[1];
                        break;
                    }
                }
            }

            // 3. HTML Fallbacks (Specific site patterns)
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

            if (! $image) {
                if (preg_match('/<img[^>]+data-testid="jum-product-image"[^>]+src="([^"]+)"/is', $html, $matches) ||
                    preg_match('/<img[^>]+src="([^"]+)"[^>]+data-testid="jum-product-image"/is', $html, $matches) ||
                    preg_match('/class="product-image"[^>]*>.*?<img[^>]+src="([^"]+)"/is', $html, $matches)) {
                    $image = $matches[1];
                }
            }

            // Final check: Ensure image doesn't look like an HTML page
            if ($image && preg_match('/\.(html|php|aspx|jsp)($|\?)/i', $image)) {
                $image = null;
            }

            // Clean up price string
            if ($price) {
                $price = preg_replace('/[^0-9.]/', '', $price);
            }

            process_image:
            $base64Image = null;
            if ($image) {
                if (str_starts_with($image, '/')) {
                    $parsedUrl = parse_url($request->url);
                    $image = ($parsedUrl['scheme'] ?? 'https').'://'.($parsedUrl['host'] ?? '').$image;
                }

                try {
                    $imageResponse = Http::timeout(10)->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                        'Accept-Language' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
                        'Referer' => $request->url,
                        'Sec-Ch-Ua' => '"Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
                        'Sec-Ch-Ua-Mobile' => '?0',
                        'Sec-Ch-Ua-Platform' => '"Windows"',
                    ])->get($image);

                    if ($imageResponse->successful() && str_starts_with($imageResponse->header('Content-Type'), 'image/')) {
                        $base64Image = 'data:'.$imageResponse->header('Content-Type').';base64,'.base64_encode($imageResponse->body());
                    }
                } catch (\Exception $e) {
                }
            }

            if ($price || $image || $name) {
                return response()->json([
                    'price' => $price ? (float) $price : null,
                    'image' => $image,
                    'image_base64' => $base64Image,
                    'name' => $name ? html_entity_decode($name) : null,
                    'quantity' => $quantity,
                    'store_name' => $storeName,
                ]);
            }

            return response()->json(['error' => 'Could not find product data on the page.'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred during scraping: '.$e->getMessage()], 500);
        }
    }
}
