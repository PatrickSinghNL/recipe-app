import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useFavoritesStore = defineStore('favorites', () => {
    const favoriteIds = ref<number[]>(JSON.parse(localStorage.getItem('favorites') || '[]'));

    const isFavorite = (id: number) => favoriteIds.value.includes(id);

    const toggleFavorite = (id: number) => {
        const index = favoriteIds.value.indexOf(id);

        if (index > -1) {
            favoriteIds.value.splice(index, 1);
        } else {
            favoriteIds.value.push(id);
        }

        localStorage.setItem('favorites', JSON.stringify(favoriteIds.value));
    };

    return {
        favoriteIds,
        isFavorite,
        toggleFavorite,
    };
});
