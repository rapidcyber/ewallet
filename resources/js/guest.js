import "./bootstrap";

/**
 * Uncomment Alpine from CSP to check additional alpine codes if they adhere to CSP rules.
 */
// import Alpine from "@alpinejs/csp";
import Swiper from "swiper";

import { Navigation, Pagination, Autoplay, Thumbs } from "swiper/modules";

// window.Alpine = Alpine;
Swiper.use([Navigation, Pagination, Autoplay, Thumbs]);
// Alpine.start();


(function(w, d, s, l, i) {
    w[l] = w[l] || [];
    w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
    });
    var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
    j.async = true;
    j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
    var n = d.querySelector('[nonce]');
    n && j.setAttribute('nonce', n.nonce || n.getAttribute('nonce'));
    f.parentNode.insertBefore(j, f);
})(window, document, 'script', 'dataLayer', 'GTM-NL49PR52')