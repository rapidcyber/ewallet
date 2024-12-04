import Swiper from 'swiper';

const swiperRepayFeatures = new Swiper('.swiper-repay-features' , {
    speed: 400,
    spaceBetween: 10,
    direction: 'horizontal',
    slidesPerView: 1,
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
  
});