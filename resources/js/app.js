import "./bootstrap";

import Swiper from "swiper";
import sort from "@alpinejs/sort";

import { Chart, registerables } from "chart.js";

import { Navigation, Pagination, Autoplay, Thumbs } from "swiper/modules";

Alpine.plugin(sort);
Swiper.use([Navigation, Pagination, Autoplay, Thumbs]);
Chart.register(...registerables);
window.Chart = Chart;
