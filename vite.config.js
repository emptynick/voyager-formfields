import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    build: {
        cssCodeSplit: true,
        lib: {
            entry: 'src/main.js',
            name: 'Formfields',
            formats: ['umd'],
            fileName: 'formfields'
        },
        rollupOptions: {
            external: ['vue', 'axios'],
            output: {
                globals: {
                    vue: 'Vue',
                    axios: 'axios'
                }
            }
        }
    }
})
