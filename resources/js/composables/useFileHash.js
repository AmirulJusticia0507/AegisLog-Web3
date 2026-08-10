import { ref } from 'vue';

export function useFileHash() {
    const hashing = ref(false);
    const progress = ref(0);

    async function computeHash(file) {
        hashing.value = true;
        progress.value = 0;

        const buffer = await file.arrayBuffer();
        const digest = await crypto.subtle.digest('SHA-256', buffer);

        progress.value = 100;
        hashing.value = false;

        return [...new Uint8Array(digest)]
            .map((byte) => byte.toString(16).padStart(2, '0'))
            .join('');
    }

    return { hashing, progress, computeHash };
}
