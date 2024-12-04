import Swiper from 'swiper';

const swiper = new Swiper('.swiper', {
    speed: 400,
    spaceBetween: 10,
    direction: 'horizontal',
    slidesPerView: 'auto',
    autoplay: {
        delay: 3000,
    },
    pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
        clickable: true,
    },
});