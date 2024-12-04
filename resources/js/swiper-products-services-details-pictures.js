import Swiper from 'swiper';

const swiperProductsServicesDetailsPictures = new Swiper('.swiper-products-services-details-pictures' , {
    speed: 400,
    spaceBetween: 10,
    direction: 'horizontal',
    slidesPerView: 6,
    navigation: {
        nextEl: '.swiper-button-products-services-pictures-next',
        prevEl: '.swiper-button-products-services-pictures-prev',
    },
});