import Swiper from 'swiper';

const swiperPreviousWorks = new Swiper('.swiper-previous-works' , {
    speed: 400,
    spaceBetween: 10,
    direction: 'horizontal',
    slidesPerView: 6,
    navigation: {
        nextEl: '.swiper-button-previous-works-next',
        prevEl: '.swiper-button-previous-works-prev',
    },
});