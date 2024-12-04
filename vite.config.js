import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/guest.js",
                "resources/js/swiper-repay-features.js",
                "resources/js/swiper-repay-about.js",
                "resources/js/swiper-operating-days.js",
                "resources/js/leaflet-map.js",
                "resources/js/swiper-previous-works.js",
                "resources/js/swiper-products-services-details-pictures.js",
            ],
            refresh: true,
        }),
    ],
});
