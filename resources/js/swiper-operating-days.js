import Swiper from 'swiper';

const operatingDaysSlider = new Swiper('.operatingDaysSlider', {
    speed: 400,
    spaceBetween: 10,
    direction: 'horizontal',
    slidesPerView: 3,
    navigation: {
        nextEl: '.operating-days-button-next',
        prevEl: '.operating-days-button-prev',
    },
});