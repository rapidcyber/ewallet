
<div>
    <x-guest.hero-section-wrapper>
        <livewire:components.layout.guest.guest-navigation whiteLogo="true" whiteText="true" />
        <div class="flex md:flex-row-reverse md:gap-0 p-5 flex-col-reverse gap-5 max-w-6xl items-center h-full">
            <div class="basis-3/5 flex-md-12">
                <div class="w-full h-full">
                    <img src="{{ url('/images/guest/repay-complete-digital-wallet-feature.png') }}" alt="RePay's Complete Digital Mobile Wallet Feature" class="w-full h-full">
                </div>
            </div>
            <div class="flex flex-col p-5 justify-center items-center md:items-start gap-2 basis-2/5 leading-7 space-y-3 flex-md-12">
                <h1 class="text-4xl font-bold text-white text-center md:text-left">RePay your Bills the Smartest Way!</h1>
                <p class="text-white tracking-wide text-center md:text-left">RePay is a new Digital Mobile Wallet giving OFWs, businesses and
                    individuals an easier alternative to traditional payment methods.</p>
                <x-button.filled-button color="red" size="lg" onclick="document.getElementById('downloap-app-section').scrollIntoView();" class="w-max">Download
                    RePay</x-button.filled-button>
            </div>
        </div>
    </x-guest.hero-section-wrapper>

    <div class="max-w-6xl mx-auto py-12">
        <h1 class="p-5 text-4xl font-bold text-center text-rp-neutral-700">What makes RePay Different?</h1>
        <div class="hidden lg:flex flex-row items-center gap-1 px-2">
            <div class="h-full flex flex-col flex-1 gap-14">
                <div class="space-y-2">
                    <div>
                       <x-guest.money-request />
                    </div>
                    <p class="font-bold text-lg">Money Request</p>                    
                </div>
                <div class="space-y-2">
                    <div>
                        <x-guest.secure-bill />
                    </div>
                    <p class="font-bold text-lg">Secured Bill Uploading</p>                    
                </div>
                <div class="space-y-2">
                    <div>
                        <x-guest.spending-alert />
                    </div>
                    <p class="font-bold text-lg">Get Spending Alert</p>                    
                </div>
            </div>
            <div class="max-w-[35rem]">
                <img src="{{ url('/images/guest/repay-reports-and-remits-interface.png') }}" class="w-full" alt="Repay's Reports and Remits Mobile App Interface">
            </div>
            <div class="h-full flex flex-col flex-1 gap-14">
                <div class="flex flex-col items-end space-y-2">
                    <div>
                        <x-guest.fast-e-bills-delivery />
                    </div>
                    <p class="font-bold text-lg">Fast E-Bills Delivery</p>                    
                </div>
                <div class="flex flex-col items-end space-y-2">
                    <div>
                       <x-guest.bill-arrival-notification />
                    </div>
                    <p class="font-bold text-lg">Bill Arrival Notifications</p>                    
                </div>
                <div class="flex flex-col items-end space-y-2">
                    <div>
                       <x-guest.lost-bills-tracker />
                    </div>
                    <p class="font-bold text-lg">Lost Bills Tracker</p>                    
                </div>
            </div>
        </div>

        @vite(['resources/js/swiper-repay-features.js'])

        <div class="block lg:hidden">
            <div class="max-w-[30rem] mx-auto">
                <img src="{{ url('/images/guest/repay-reports-and-remits-interface.png') }}" class="w-full" alt="Repay's Reports and Remits Mobile App Interface">
            </div>
            <div class="swiper-repay-features relative px-4 mt-8">
                <div class="absolute z-30 bottom-[50%] left-0 right-0 my-auto flex flex-row justify-between w-full">
                    <div class="swiper-button-prev cursor-pointer shadow-md flex items-center justify-center  rounded-full !w-9 !h-9 !bg-white text-gray-600">   
                    </div>
                    <div class="swiper-button-next cursor-pointer shadow-md flex items-center justify-center rounded-full !w-9 !h-9 !bg-white text-gray-600">
                    </div>
                </div>
                <div class="swiper-wrapper flex flex-row">
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div class="mx-auto">
                            <svg  width="55" height="48" viewBox="0 0 55 48" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.1458 41.514V24.2617H20.4657C23.5682 24.8505 26.6706 26.6355 29.7731 28.7056H35.458C38.0317 28.8692 39.3802 31.6355 36.8771 33.4533C34.8851 35.0047 32.2542 34.9159 29.5572 34.6589C27.6975 34.5607 27.6181 37.2103 29.5572 37.2196C30.2314 37.2757 30.963 37.1075 31.602 37.1075C34.9645 37.1028 37.7364 36.4206 39.4331 33.6028L40.2836 31.4953L48.7405 27.0514C52.9711 25.5748 55.981 30.2664 52.8609 33.5327C46.7353 38.257 40.4555 42.1449 34.0302 45.285C29.3633 48.2944 24.6964 48.1916 20.0338 45.285L13.1458 41.514ZM18.6897 0.5L50.1595 9.43925L45.7746 26.7897L14.3048 17.8505L18.6897 0.5ZM33.5278 8.54673C36.1896 9.30374 37.7673 12.1963 37.0533 15.0187C36.3394 17.8411 33.6115 19.514 30.9498 18.757C28.288 18 26.7103 15.1075 27.4242 12.285C28.1337 9.46262 30.8616 7.78972 33.5278 8.54673ZM23.2817 4.63551L44.2058 10.5794C43.8488 11.9907 44.642 13.4533 45.9773 13.8271L44.2454 20.6869C42.9145 20.3084 41.5352 21.1495 41.1826 22.5654L20.2542 16.6215C20.6111 15.2103 19.8179 13.7523 18.4826 13.3738L20.2145 6.51402C21.5454 6.89252 22.9248 6.0514 23.2817 4.63551ZM0 22.6028H10.9424V43.2477H0V22.6028Z" fill="#E31C79"/>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Money Request</p>                    
                    </div>
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div>
                            <svg  width="60" height="48" viewBox="0 0 60 48" fill="none">
                                <path d="M2.77787 6.5164H29.8535C29.3749 7.57611 28.9989 8.67989 28.7307 9.81239H3.29536V44.1743H51.4906V31.8119C52.6198 31.4857 53.7152 31.0508 54.7616 30.5131V44.7154C54.7641 45.0809 54.6941 45.4433 54.5558 45.7812C54.4175 46.1191 54.2136 46.4257 53.9561 46.6832C53.9127 46.7317 53.8635 46.7747 53.8096 46.8111C53.3098 47.2627 52.6599 47.5085 51.9886 47.4998H2.75834C2.39538 47.5008 2.03582 47.4292 1.70058 47.289C1.36534 47.1488 1.06109 46.9429 0.805533 46.6832C0.289891 46.16 0.000346181 45.4527 0 44.7154V9.29585C-5.7002e-07 8.93173 0.0712549 8.57118 0.209689 8.23483C0.348123 7.89849 0.551018 7.59295 0.806765 7.3357C1.06251 7.07846 1.36609 6.87455 1.70012 6.73565C2.03416 6.59676 2.3921 6.5256 2.75346 6.52624L2.77787 6.5164ZM46.4768 0.500001C49.1509 0.499028 51.7652 1.29723 53.989 2.79363C56.2128 4.29004 57.9463 6.41742 58.97 8.90667C59.9938 11.3959 60.2619 14.1352 59.7405 16.778C59.219 19.4209 57.9314 21.8485 56.0406 23.7538C54.1497 25.6591 51.7405 26.9566 49.1178 27.482C46.495 28.0075 43.7766 27.7373 41.3062 26.7057C38.8359 25.6741 36.7246 23.9274 35.2396 21.6865C33.7546 19.4457 32.9624 16.8114 32.9634 14.1168C32.9634 10.5054 34.3871 7.04194 36.9214 4.48828C39.4556 1.93463 42.8928 0.500001 46.4768 0.500001ZM44.524 20.6695H48.4638C48.8397 20.6695 49.2003 20.5193 49.4666 20.2519C49.7329 19.9845 49.8832 19.6217 49.8845 19.2428V14.7219H52.3841C52.5791 14.7309 52.7732 14.6905 52.9489 14.6046C53.1245 14.5187 53.2761 14.3899 53.3897 14.23C53.9121 13.438 53.1993 12.6558 52.6965 12.0999C51.2661 10.5552 48.0781 7.72657 47.3605 6.8952C47.2531 6.75714 47.116 6.64549 46.9596 6.56871C46.8031 6.49193 46.6313 6.45203 46.4573 6.45203C46.2832 6.45203 46.1115 6.49193 45.955 6.56871C45.7985 6.64549 45.6614 6.75714 45.5541 6.8952C44.8511 7.74625 41.502 10.7471 40.1351 12.2672C39.6469 12.7935 39.0903 13.5167 39.5785 14.2349C39.6897 14.3903 39.8366 14.5162 40.0067 14.6019C40.1767 14.6876 40.3648 14.7304 40.5549 14.7269H43.0692V19.2674C43.0769 19.6463 43.2333 20.0066 43.5042 20.2694C43.775 20.5322 44.1383 20.6761 44.5142 20.6695H44.524ZM14.1627 16.1338C14.9453 16.1328 15.7105 16.3658 16.3615 16.8033C17.0126 17.2408 17.5202 17.863 17.8201 18.5913C18.12 19.3197 18.1987 20.1212 18.0463 20.8947C17.8939 21.6681 17.5172 22.3785 16.9638 22.9361C16.4105 23.4937 15.7054 23.8733 14.9379 24.0269C14.1703 24.1805 13.3748 24.1011 12.6521 23.7989C11.9293 23.4967 11.3117 22.9852 10.8776 22.3292C10.4434 21.6732 10.2122 20.9021 10.2132 20.1136C10.2132 19.0581 10.6293 18.0458 11.37 17.2994C12.1107 16.5531 13.1152 16.1338 14.1627 16.1338ZM30.7616 33.2188L34.0228 27.5566C36.6811 30.0639 40.0244 31.7107 43.6208 32.2841L47.0724 41.2374H7.89422V38.1972L11.1847 38.03L14.4752 29.9031L16.1155 35.708H21.061L25.3426 24.605L30.7616 33.2188Z" fill="#E31C79"/>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Secured Bill Uploading</p>                    
                    </div>
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div>
                            <svg  width="30" height="48" viewBox="0 0 30 48" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.78291 0.500013H21.6347C22.9026 0.501027 24.1182 1.03663 25.0144 1.98909C25.9106 2.94156 26.414 4.23295 26.414 5.57944V7.87436C25.9394 7.77639 25.457 7.72644 24.9734 7.72519H24.7681V6.03842H1.64593V40.4622H24.7609V33.5851H24.9662C25.4499 33.5858 25.9324 33.5358 26.4068 33.436V42.4206C26.4068 43.7671 25.9034 45.0585 25.0072 46.0109C24.111 46.9634 22.8954 47.499 21.6275 47.5H4.78291C3.5144 47.5 2.29785 46.9648 1.40088 46.0123C0.503914 45.0597 1.36847e-06 43.7677 1.36847e-06 42.4206V5.57179C-0.000474124 4.90497 0.122968 4.2446 0.363251 3.62854C0.603535 3.01248 0.955938 2.45283 1.40026 1.98167C1.84458 1.51051 2.37209 1.13711 2.95255 0.882854C3.53302 0.628602 4.15502 0.498503 4.78291 0.500013ZM13.1998 42.0343C13.5888 42.0343 13.9689 42.1567 14.2923 42.3862C14.6157 42.6157 14.8678 42.9419 15.0166 43.3235C15.1654 43.7051 15.2044 44.125 15.1285 44.5301C15.0526 44.9352 14.8653 45.3073 14.5903 45.5993C14.3153 45.8914 13.9649 46.0903 13.5835 46.1709C13.202 46.2515 12.8066 46.2101 12.4473 46.0521C12.088 45.894 11.7808 45.6263 11.5648 45.2829C11.3487 44.9394 11.2334 44.5357 11.2334 44.1226C11.2329 43.8483 11.2834 43.5765 11.3821 43.3229C11.4807 43.0693 11.6256 42.8388 11.8083 42.6448C11.991 42.4508 12.2079 42.297 12.4467 42.1922C12.6855 42.0874 12.9415 42.0338 13.1998 42.0343Z" fill="#E31C79"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M24.9706 10.761C25.5671 10.758 26.1582 10.8804 26.7098 11.1213C27.2615 11.3622 27.7627 11.7167 28.1846 12.1644C28.6066 12.6122 28.9408 13.1442 29.1681 13.7298C29.3953 14.3155 29.5111 14.9431 29.5087 15.5765V25.743C29.5111 26.3766 29.3953 27.0044 29.1681 27.5903C28.9409 28.1761 28.6067 28.7084 28.1849 29.1564C27.763 29.6045 27.2618 29.9594 26.7101 30.2007C26.1584 30.442 25.5673 30.5649 24.9706 30.5624H21.2753C19.3773 33.1135 16.9101 34.6473 13.9892 35.4773C13.8721 35.5119 13.7483 35.5109 13.6318 35.4744C13.5152 35.4379 13.4107 35.3673 13.3301 35.2708C13.2739 35.2042 13.2307 35.1264 13.203 35.0419C13.1753 34.9575 13.1637 34.8681 13.1687 34.7788C13.1737 34.6895 13.1953 34.6022 13.2323 34.5219C13.2693 34.4415 13.3209 34.3698 13.3841 34.3107C13.7299 33.9931 14.0448 33.6394 14.3241 33.2551C14.8579 32.4295 15.2561 31.5139 15.5019 30.5471H11.9002C10.7036 30.542 9.55761 30.0336 8.71315 29.1332C7.86869 28.2329 7.39456 27.0138 7.39457 25.743V15.5689C7.39219 14.9356 7.50783 14.3081 7.73482 13.7225C7.96181 13.1369 8.29567 12.6048 8.71716 12.1568C9.13865 11.7088 9.63945 11.3539 10.1907 11.1123C10.7419 10.8708 11.3327 10.7475 11.929 10.7495C16.7156 10.7495 20.184 10.7495 24.9706 10.7495V10.761Z" fill="#E31C79"/>
                                <path d="M20.0464 25.9273C19.9783 26.3163 19.7847 26.668 19.4989 26.9218C19.2795 27.1171 19.0157 27.2478 18.7338 27.3008C18.4519 27.3538 18.1618 27.3273 17.8926 27.224C17.7141 27.1565 17.5492 27.054 17.4064 26.9218C17.2616 26.7943 17.1396 26.6401 17.0462 26.4666C16.9518 26.294 16.8872 26.1049 16.8553 25.9082C16.848 25.8606 16.8586 25.8118 16.8849 25.7724C16.9112 25.7331 16.951 25.7063 16.9958 25.6978H19.8771C19.9038 25.698 19.93 25.7046 19.9538 25.7173C19.9776 25.73 19.9984 25.7483 20.0144 25.7709C20.0304 25.7935 20.0413 25.8197 20.0463 25.8475C20.0512 25.8753 20.05 25.9039 20.0428 25.9312L20.0464 25.9273ZM14.8528 14.5407C14.9364 14.5057 15.0296 14.5074 15.112 14.5454C15.1944 14.5834 15.2593 14.6547 15.2922 14.7434C15.3252 14.8322 15.3236 14.9312 15.2878 15.0187C15.252 15.1062 15.1849 15.1751 15.1013 15.2101C14.7381 15.3516 14.4118 15.5829 14.1494 15.8848C13.887 16.1868 13.696 16.5507 13.5922 16.9465C13.5795 16.9917 13.5584 17.0337 13.5303 17.0701C13.5021 17.1065 13.4674 17.1366 13.4282 17.1587C13.389 17.1808 13.3461 17.1943 13.3019 17.1986C13.2577 17.2029 13.2132 17.1978 13.1708 17.1837C13.0849 17.1563 13.0127 17.0937 12.9702 17.0098C12.9276 16.9259 12.9182 16.8275 12.9439 16.7362C13.0831 16.2333 13.3296 15.7712 13.6643 15.386C13.9973 15.0133 14.4046 14.7245 14.8564 14.5407H14.8528ZM21.8004 15.2101C21.7168 15.1751 21.6497 15.1062 21.614 15.0187C21.5782 14.9312 21.5766 14.8322 21.6095 14.7434C21.6425 14.6547 21.7073 14.5834 21.7897 14.5454C21.8721 14.5074 21.9653 14.5057 22.0489 14.5407C22.507 14.722 22.9184 15.0151 23.25 15.3965C23.5817 15.7779 23.8241 16.2368 23.9578 16.7362C23.9791 16.8279 23.9678 16.9247 23.9259 17.0081C23.884 17.0914 23.8145 17.1553 23.7309 17.1875C23.6449 17.2149 23.5522 17.2048 23.4732 17.1597C23.3942 17.1145 23.3353 17.0378 23.3095 16.9465C23.2041 16.5515 23.0126 16.1884 22.7504 15.8867C22.4882 15.585 22.1627 15.3532 21.8004 15.2101ZM19.7691 15.7226C19.962 15.804 20.1486 15.9012 20.3273 16.0133H20.3489C20.5163 16.1278 20.6752 16.2556 20.8243 16.3958C21.1389 16.6799 21.4056 17.0188 21.6131 17.3979C21.7145 17.5883 21.8024 17.7865 21.876 17.9907C21.9521 18.1998 22.0088 18.4163 22.0453 18.6371C22.0846 18.8629 22.1039 19.0922 22.1029 19.3218V20.06C22.1029 20.2971 22.1029 20.5266 22.1029 20.7523C22.1042 20.9568 22.1162 21.1611 22.139 21.3643C22.162 21.5628 22.1969 21.7595 22.2434 21.9533C22.286 22.1379 22.3464 22.3173 22.4235 22.4888C22.4989 22.6715 22.5893 22.8469 22.6936 23.0128C22.8163 23.1911 22.955 23.3564 23.1078 23.5062C23.2946 23.6843 23.4932 23.8479 23.7021 23.9958C23.7644 24.0402 23.8076 24.1088 23.8225 24.1869C23.8373 24.265 23.8225 24.3461 23.7813 24.4127C23.7549 24.4539 23.7195 24.4876 23.6781 24.5109C23.6366 24.5342 23.5905 24.5465 23.5436 24.5466H13.3473C13.2858 24.5459 13.2261 24.524 13.1773 24.4842C13.1285 24.4445 13.0931 24.389 13.0764 24.3261C13.0598 24.2632 13.0628 24.1963 13.085 24.1353C13.1072 24.0744 13.1475 24.0227 13.1996 23.9881C13.4077 23.8407 13.6051 23.6771 13.7903 23.4985C13.9437 23.349 14.0814 23.1823 14.2009 23.0013C14.3037 22.839 14.3952 22.669 14.4746 22.4926C14.5502 22.32 14.6117 22.1409 14.6583 21.9571C14.7086 21.7625 14.7459 21.5644 14.77 21.3643C14.795 21.1588 14.807 20.9518 14.806 20.7446V19.3218C14.8053 19.0922 14.8246 18.863 14.8636 18.6371C14.9021 18.4152 14.96 18.1975 15.0365 17.9869C15.1091 17.7803 15.1983 17.5807 15.303 17.3902C15.5088 17.0109 15.7744 16.672 16.0882 16.3881C16.4072 16.0955 16.7732 15.8661 17.1687 15.7111C17.305 15.6567 17.4446 15.612 17.5865 15.5772C17.6271 15.4071 17.7149 15.2539 17.8386 15.1374C18.0172 14.9747 18.2484 14.8924 18.4833 14.9079C18.7167 14.8998 18.9447 14.9828 19.1244 15.1412C19.6934 15.6843 18.9839 15.386 19.7835 15.7226H19.7691Z" fill="white"/>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Money Request</p>                    
                    </div>
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div>
                            <svg  width="60" height="48" viewBox="0 0 60 48" fill="none">
                                <path d="M41.7795 8.81691C46.5033 9.3466 50.7387 11.3672 53.9353 14.3636C57.6814 17.8761 59.9982 22.7292 59.9982 28.0883C59.9982 33.4483 57.6818 38.3013 53.9353 41.8143C50.1888 45.3272 45.013 47.5 39.2965 47.5C33.58 47.5 28.4052 45.3272 24.6587 41.8143C20.9122 38.3013 18.5953 33.4483 18.5953 28.0883C18.5953 22.7287 20.9126 17.8761 24.6587 14.3636C28.0903 11.146 32.722 9.05268 37.8687 8.72379V5.46798C37.8687 5.41846 37.8716 5.36985 37.8755 5.32125H33.9977C33.3615 5.32125 32.8422 4.83335 32.8422 4.23734V1.58391C32.8422 0.987894 33.3615 0.5 33.9977 0.5H45.6511C46.2872 0.5 46.8075 0.987894 46.8075 1.58391V4.23734C46.8075 4.83381 46.2872 5.32125 45.6511 5.32125H41.7732C41.7781 5.3694 41.78 5.41846 41.78 5.46798L41.7795 8.81691ZM40.9593 25.1891C42.0595 25.7447 42.8071 26.8349 42.8071 28.0887C42.8071 29.9067 41.2354 31.3813 39.2965 31.3813C37.3576 31.3813 35.7859 29.9067 35.7859 28.0887C35.7859 26.8349 36.5345 25.7447 37.6347 25.1891V17.6462C37.6347 16.7854 38.3784 16.0876 39.2965 16.0876C40.2146 16.0876 40.9593 16.7854 40.9593 17.6462V25.1891ZM5.56504 38.931C4.64694 38.931 3.90277 38.2327 3.90277 37.3719C3.90277 36.511 4.64694 35.8137 5.56504 35.8137H14.9534C15.8715 35.8137 16.6152 36.511 16.6152 37.3719C16.6152 38.2327 15.8711 38.931 14.9534 38.931H5.56504ZM2.12664 29.7664C1.20853 29.7664 0.464844 29.0691 0.464844 28.2082C0.464844 27.3473 1.20902 26.6491 2.12664 26.6491H13.0019C13.9195 26.6491 14.6637 27.3473 14.6637 28.2082C14.6637 29.0691 13.9195 29.7664 13.0019 29.7664H2.12664ZM5.48123 20.8398C4.56312 20.8398 3.81943 20.142 3.81943 19.2816C3.81943 18.4212 4.56361 17.723 5.48123 17.723H14.7974C15.7155 17.723 16.4597 18.4208 16.4597 19.2816C16.4597 20.1425 15.7155 20.8398 14.7974 20.8398H5.48123ZM57.8897 14.6803C58.693 12.8822 58.5975 10.9988 57.4478 9.73682C56.068 8.22272 53.5739 8.04918 51.2575 9.11446C53.711 10.6922 55.9387 12.5274 57.8897 14.6803ZM20.7043 14.6803C19.9005 12.8822 19.996 10.9988 21.1457 9.73682C22.5255 8.22272 25.0196 8.04918 27.336 9.11446C24.883 10.6922 22.6549 12.5274 20.7043 14.6803ZM51.5851 16.5673C48.4407 13.6186 44.0963 11.7952 39.297 11.7952C34.4986 11.7952 30.1538 13.6191 27.0094 16.5673C23.8646 19.5156 21.9199 23.5896 21.9199 28.0887C21.9199 32.5888 23.8651 36.6623 27.0094 39.6106C30.1538 42.5588 34.4981 44.3832 39.2965 44.3832C44.0958 44.3832 48.4402 42.5588 51.5846 39.6106C54.7289 36.6623 56.6751 32.5888 56.6751 28.0883C56.6751 23.5891 54.7294 19.5156 51.5851 16.5673Z" fill="#E31C79"/>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Fast E-Bills Deliver</p>                    
                    </div>
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div>
                            <svg  width="44" height="48" viewBox="0 0 44 48" fill="none">
                                <g clip-path="url(#clip0_17477_18051)">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M32.9164 17.5863H22.9167V15.7083H32.9164V17.5863ZM11.9494 14.3198C11.7881 14.5786 11.5604 14.7922 11.2885 14.9395C11.0582 15.0652 10.801 15.1372 10.5372 15.1498C10.2733 15.1607 10.0108 15.1067 9.77402 14.993C9.60588 14.9049 9.45426 14.7898 9.32557 14.6526L11.9494 14.316V14.3198ZM12.2208 12.9008V13.1226C12.2287 13.2322 12.2287 13.3421 12.2208 13.4516L12.1658 13.6887L8.98726 14.098L8.93219 13.9718L8.81811 13.4784V13.333L12.2169 12.9008H12.2208ZM12.6142 5.42701C12.9399 5.62693 13.2329 5.87319 13.4836 6.15756C13.7418 6.44773 13.9467 6.77895 14.0894 7.13673C14.1709 7.34174 14.2354 7.55278 14.2821 7.76783C14.3315 7.99547 14.3566 8.22748 14.3569 8.46013C14.3596 8.71697 14.3346 8.97337 14.2821 9.22511C14.2267 9.48723 14.145 9.74345 14.0382 9.99008C13.7724 10.5362 13.441 11.0497 13.0508 11.52L13.0115 11.5659C12.8424 11.7878 12.6732 12.0058 12.5316 12.2238C12.4697 12.3143 12.3844 12.3874 12.2843 12.4357C12.1842 12.4841 12.0728 12.506 11.9612 12.4992L9.12101 12.8587C8.97527 12.8778 8.82731 12.8459 8.70343 12.7689C8.57955 12.6919 8.48777 12.5746 8.4444 12.438C8.37625 12.2356 8.29208 12.0387 8.19264 11.849V11.826C8.11563 11.6807 8.02335 11.5436 7.91728 11.4168C7.77173 11.2485 7.62225 11.0763 7.4767 10.8736L7.44916 10.8277C7.29544 10.6084 7.16372 10.3753 7.05578 10.1316C6.92354 9.86456 6.81942 9.5852 6.74501 9.29778C6.68063 9.00375 6.65289 8.70324 6.66241 8.40276C6.67143 8.0936 6.71898 7.78664 6.80402 7.48862C6.89881 7.17517 7.03082 6.87352 7.1974 6.58977L7.22493 6.54005C7.43552 6.20331 7.70127 5.90228 8.01169 5.64885L8.04709 5.62208C8.33882 5.38355 8.66826 5.1924 9.02267 5.056C9.31137 4.94803 9.61258 4.87482 9.91957 4.83798C10.2304 4.79974 10.545 4.79974 10.8558 4.83798C11.1613 4.86851 11.4621 4.93267 11.7527 5.02923C12.0429 5.1269 12.3212 5.25516 12.5827 5.41171L12.6221 5.43849L12.6142 5.42701ZM11.0407 8.10442H11.7802C11.831 8.1064 11.8791 8.12742 11.9143 8.16307C11.9495 8.19872 11.9691 8.24624 11.9691 8.29566C11.9683 8.33278 11.9559 8.36878 11.9337 8.39894L10.1281 11.2829C10.1051 11.3229 10.0678 11.3534 10.0233 11.3685C9.97874 11.3836 9.93006 11.3824 9.88644 11.3649C9.84281 11.3475 9.80728 11.3151 9.78656 11.2739C9.76585 11.2327 9.76138 11.1855 9.77402 11.1414L10.0651 9.35898H9.21149C9.18636 9.35898 9.16148 9.35411 9.13831 9.34465C9.11514 9.33518 9.09415 9.32131 9.07656 9.30386C9.05898 9.2864 9.04515 9.26571 9.0359 9.24299C9.02664 9.22027 9.02214 9.19599 9.02267 9.17156C9.02149 9.13769 9.03111 9.10429 9.0502 9.07594L10.8243 6.19581C10.8513 6.15304 10.8944 6.12226 10.9444 6.11009C10.9944 6.09793 11.0474 6.10536 11.0918 6.13079C11.1241 6.15091 11.1498 6.17963 11.1659 6.21352C11.1819 6.24742 11.1876 6.28508 11.1823 6.32203L11.0604 8.09295L11.0407 8.10442ZM10.5096 1.90431C12.152 1.90431 13.7576 2.37786 15.1232 3.26507C16.4888 4.15229 17.5532 5.41332 18.1817 6.88871C18.8102 8.36409 18.9747 9.98756 18.6543 11.5538C18.3338 13.1201 17.5429 14.5588 16.3816 15.688C15.2202 16.8172 13.7406 17.5862 12.1297 17.8978C10.5188 18.2093 8.84915 18.0494 7.33176 17.4383C5.81436 16.8272 4.51743 15.7923 3.60495 14.4644C2.69248 13.1366 2.20544 11.5756 2.20544 9.97861C2.20544 7.83717 3.08035 5.78344 4.63768 4.26921C6.19502 2.75499 8.30723 1.90431 10.5096 1.90431ZM3.6334 3.28891C5.23066 1.74488 7.32908 0.787059 9.57118 0.578631C11.8133 0.370203 14.0603 0.924058 15.9296 2.14584C17.7988 3.36762 19.1745 5.18175 19.8224 7.27916C20.4703 9.37657 20.3502 11.6275 19.4827 13.6485C18.6151 15.6695 17.0538 17.3355 15.0646 18.3628C13.0754 19.39 10.7815 19.7149 8.57359 19.282C6.3657 18.8492 4.38043 17.6855 2.95601 15.9891C1.53159 14.2927 0.756133 12.1686 0.761749 9.97861C0.761195 8.73356 1.01355 7.50067 1.50432 6.3507C1.99509 5.20072 2.71462 4.15632 3.6216 3.27744L3.6334 3.28891ZM38.0539 23.4154V8.17327H22.842C22.7474 7.53642 22.5987 6.90827 22.3975 6.29526H39.0216C39.1482 6.29526 39.2735 6.3195 39.3904 6.36659C39.5074 6.41368 39.6136 6.48271 39.7031 6.56973C39.7926 6.65674 39.8636 6.76005 39.912 6.87374C39.9604 6.98743 39.9854 7.10929 39.9854 7.23235V24.9338L39.0452 24.0197C38.7669 23.7469 38.4277 23.5401 38.0539 23.4154ZM6.19035 21.3576V38.187L6.87876 37.8045C7.06529 37.699 7.28275 37.6571 7.49658 37.6855C7.71041 37.7139 7.90834 37.811 8.05889 37.9614L11.0446 40.7229L14.1562 37.9843C14.3333 37.8263 14.5649 37.7386 14.8053 37.7386C15.0457 37.7386 15.2773 37.8263 15.4544 37.9843L18.4637 40.7497L21.355 38.0226C21.4982 37.8897 21.6791 37.8018 21.8743 37.7701C21.8625 38.105 21.9202 38.4388 22.0439 38.7516C22.1676 39.0644 22.3547 39.3498 22.5942 39.5908C22.4624 39.7592 22.3551 39.9445 22.2755 40.1415L22.0631 39.9656L19.1443 42.7157C18.9644 42.8819 18.7257 42.9747 18.4775 42.9747C18.2293 42.9747 17.9906 42.8819 17.8107 42.7157L14.7935 39.9541L11.7173 42.6315C11.5382 42.8007 11.2983 42.8953 11.0486 42.8953C10.7988 42.8953 10.559 42.8007 10.3798 42.6315L7.2328 39.7361L5.76551 40.5814C5.62104 40.6756 5.45313 40.7302 5.27944 40.7395C5.10574 40.7488 4.93269 40.7123 4.7785 40.634C4.6243 40.5557 4.49466 40.4385 4.40322 40.2946C4.31177 40.1507 4.26191 39.9855 4.25887 39.8164V20.4702C4.87568 20.8187 5.52183 21.1156 6.19035 21.3576ZM44.0057 35.4408L31.2209 47.5006L24.6437 41.0978L25.3872 40.1607L30.206 44.8155L30.265 44.8691C30.4856 45.0793 30.7822 45.1971 31.0911 45.1971C31.4 45.1971 31.6966 45.0793 31.9172 44.8691L32.3342 44.456L34.3011 42.4785L43.0104 34.4769L44.0018 35.4408H44.0057ZM43.7972 32.1935L31.0124 44.2533L24.4352 37.8466L37.216 25.7868L43.7933 32.1935H43.7972ZM35.9257 34.5419C36.0533 34.9298 36.0596 35.3457 35.9439 35.7371C35.8282 36.1285 35.5957 36.4777 35.2758 36.7405C34.9559 37.0033 34.563 37.1679 34.1468 37.2135C33.7306 37.2591 33.3099 37.1836 32.9379 36.9965C32.5659 36.8095 32.2593 36.5194 32.0569 36.1629C31.8546 35.8063 31.7656 35.3994 31.8012 34.9937C31.8368 34.588 31.9954 34.2016 32.257 33.8836C32.5185 33.5655 32.8712 33.3301 33.2704 33.207C33.8046 33.0424 34.3841 33.0905 34.8819 33.3407C35.3797 33.591 35.7551 34.0229 35.9257 34.5419ZM40.6738 33.2682L32.1375 41.201C32.01 41.0768 31.8585 40.9783 31.6918 40.911C31.5251 40.8438 31.3464 40.8092 31.1659 40.8092C30.9854 40.8092 30.8066 40.8438 30.6399 40.911C30.4732 40.9783 30.3217 41.0768 30.1942 41.201L27.5901 38.6804C27.7183 38.5567 27.82 38.4095 27.8895 38.2474C27.9589 38.0852 27.9947 37.9113 27.9947 37.7357C27.9947 37.56 27.9589 37.3861 27.8895 37.224C27.82 37.0619 27.7183 36.9147 27.5901 36.7909L36.1264 28.8505C36.2542 28.9748 36.406 29.0735 36.573 29.1407C36.7401 29.208 36.9191 29.2427 37.1 29.2427C37.2808 29.2427 37.4598 29.208 37.6269 29.1407C37.7939 29.0735 37.9457 28.9748 38.0736 28.8505L40.6738 31.3826C40.5434 31.5075 40.4399 31.6564 40.3693 31.8205C40.2986 31.9847 40.2622 32.1608 40.2622 32.3388C40.2622 32.5168 40.2986 32.6929 40.3693 32.8571C40.4399 33.0212 40.5434 33.1701 40.6738 33.295V33.2682ZM28.9905 30.0477H11.3239V28.1773H30.9928L29.0259 30.0553L28.9905 30.0477ZM32.1375 23.8132H11.3239V21.9428H32.1296V23.8323L32.1375 23.8132Z" fill="#E31C79"/>
                                </g>
                                <defs>
                                <clipPath id="clip0_17477_18051">
                                <rect width="43.24" height="47" fill="white" transform="translate(0.761719 0.5)"/>
                                </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Bill Arrival Notifications</p>                    
                    </div>
                    <div class="swiper-slide !flex !flex-col !items-center !justify-center space-y-2">
                        <div>
                            <svg  width="54" height="48" viewBox="0 0 54 48" fill="none">
                                <g clip-path="url(#clip0_17477_18055)">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.1506 17.2191C18.6363 11.0575 24.7102 7.01522 32.5265 7.87602C39.8102 8.67763 46.4389 13.8611 48.0505 21.549C49.3984 27.9868 46.9125 34.1245 42.4997 38.2769C37.3454 43.126 29.5629 45.2671 22.1853 42.016C18.4053 40.3495 15.8329 38.226 13.9092 35.491C13.713 35.2366 13.4493 34.8254 13.0834 34.5575C12.0213 33.7757 10.6238 34.7039 10.839 35.8482C10.8843 36.0933 10.9961 36.3549 11.1934 36.6301C12.7754 39.092 14.5304 40.9735 16.8233 42.6941C22.0799 46.6377 28.8752 48.4185 35.6938 47.0344C42.0461 45.7458 47.2088 41.9548 50.4077 36.8793C56.0018 28.0055 54.7267 17.325 48.9037 9.83747C45.2872 5.18668 39.9157 1.76739 33.3471 0.7768C22.257 -0.895986 11.1238 5.16592 7.55786 14.8278L3.07439 13.4156C1.83197 13.0283 0.504114 13.7053 0.110715 14.9285C-0.0645949 15.4713 -0.0308055 16.0586 0.205637 16.5785L5.33564 27.8477C5.8672 29.021 7.26466 29.5475 8.45646 29.0231L8.63048 28.938L8.63259 28.9411L19.8777 22.8314C21.0231 22.2115 21.4418 20.7952 20.8132 19.6676C20.4809 19.0715 19.923 18.6759 19.3008 18.5254L15.1506 17.2191Z" fill="#E31C79"/>
                                </g>
                                <defs>
                                <clipPath id="clip0_17477_18055">
                                <rect width="54" height="47" fill="white" transform="translate(0 0.5)"/>
                                </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <p class="font-bold text-lg">Lost Bills Tracker</p>                    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative bg-cover h-screen inset-0 bg-no-repeat flex flex-col items-center bg-center white-rays-background">
        <div class="p-5 max-w-6xl mx-auto h-screen flex justify-center items-center flex-col gap-2 text-center">
            <h1 class="text-4xl font-bold text-rp-neutral-700">RePay is secured by BSP</h1>
            <p>We prioritize your convenience. Enjoy hassle-free transactions with us!</p>
            <x-button.filled-button href="https://realholmes.ph/articles/33-bsp-awards-ops-certification-to-repay-digital-solutions" color="red" size="lg" target="_blank" class="w-[150px]" size="sm">learn
                more</x-button.filled-button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto py-12 px-5">
        <h1 class="text-4xl font-bold text-center text-rp-neutral-700">Features</h1>
        <div class="space-y-4">
            {{-- Remit --}}
            <div
                class="flex flex-col md:flex-row relative w-full bg-primary-600 text-white rounded-2xl mt-3 gap-5 md:gap-0 {{-- pb-5 md:pb-24 --}} px-5 pt-7">
                <div class="flex flex-row flex-1 md:basis-2/3">
                    <div class="flex flex-col gap-2 md:gap-0 space-y-2 flex-1 justify-center items-center md:items-start md:justify-normal {{-- basis-6/12 --}} md:basis-8/12 lg:basis-11/12">
                        <div class="w-20">
                            <img src="{{ url('/images/guest/remit.svg') }}" alt="Remit" class="w-full">
                        </div>
                        <h1 class="uppercase text-white font-bold text-2xl text-center md:text-left">remit</h1>
                        <p class="tracking-wide text-center md:text-left">
                            This classic feature allows users to Remit money from wherever they want at any time.
                        </p>
                        <x-button.filled-button href="{{ route('features.remit') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
                    </div>
                </div>
                <div class="flex items-center justify-center flex-1 md:basis-1/3 {{-- absolute md:right-14 lg:right-24 right-5 bottom-0 --}}">
                    <img src="{{ url('/images/guest/remit-interface.svg') }}" class="w-56 md:w-60 sm:w-52 {{-- w-40 --}}" alt="Repay Mobile Remit Interface">
                </div>
            </div>

            <div class="flex md:flex-row flex-col gap-4">
                {{-- Explore --}}
                <div class="bg-primary-600 text-white rounded-2xl px-5 py-7 space-y-2 flex-1 flex flex-col justify-center items-center md:justify-normal md:items-start gap-2 md:gap-0 md:basis-1/3">
                    <div class="w-20">
                        <img src="{{ url('/images/guest/explore.svg') }}" alt="Explore" class="w-full">
                    </div>
                    <h1 class="uppercase text-white font-bold text-2xl">explore</h1>
                    <p class="tracking-wide w-[70%] text-center md:text-left">Users here can look up For Sale properties, gadgets, and other
                        things they may like, leading to fruitful transactions.</p>
                    <div>
                        <x-button.filled-button href="{{ route('features.explore') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
                    </div>
                </div>
                {{-- Payments --}}
                <div class="bg-primary-600 flex flex-col gap-2 md:gap-0 justify-center items-center md:justify-normal md:items-start text-white rounded-2xl px-5 py-7 space-y-2 basis-2/3">
                    <div class="w-20">
                        <img src="{{ url('/images/guest/payments.svg') }}" alt="Payments" class="w-full">
                    </div>
                    <h1 class="uppercase text-white font-bold text-2xl">payment</h1>
                    <p class="tracking-wide w-[65%] text-center md:text-left">Featuring the excellent Bills Payment system that RePay has
                        where
                        users can easily catalog and schedule payments as they wish.</p>
                    <div>
                        <x-button.filled-button href="{{ route('features.payments') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
                    </div>
                </div>
            </div>


            <div class="flex md:flex-row flex-col gap-4">
                {{-- Assets --}}
                <div class="flex flex-col gap-2 md:gap-0 justify-center items-center md:justify-start md:items-start bg-primary-600 text-white rounded-2xl px-5 py-7 space-y-2 basis-2/3">
                    <div class="w-20">
                        <img src="{{ url('/images/guest/assets.svg') }}" alt="Assets" class="w-full">
                    </div>
                    <h1 class="uppercase text-white font-bold text-2xl">assets</h1>
                    <p class="tracking-wide w-[65%] text-center md:text-left">Serves as the inventory for RePay’s merchant marketplace where
                        users can keep tabs on everything they have put up for sale.</p>
                    <div>
                        <x-button.filled-button href="{{ route('features.assets') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
                    </div>
                </div>
                {{-- Yolo --}}
                <div class="bg-primary-600 flex flex-col gap-2 md:gap-0 items-center justify-center md:items-start md:justify-normal text-white rounded-2xl px-5 py-7 space-y-2 basis-1/3">
                    <div class="w-20">
                        <img src="{{ url('/images/guest/yolo.svg') }}" alt="Yolo" class="w-full">
                    </div>
                    <h1 class="uppercase text-white font-bold text-2xl">yolo</h1>
                    <p class="tracking-wide w-[80%] text-center md:text-left">The social messaging arm of the platform, users here can add
                        and
                        send messages to their friends and other contacts.</p>
                    <p class="tracking-wide w-[80%] text-center md:text-left">They can also claim reward points and other fabulous redeemable
                        prizes that may be offered.</p>
                    <div>
                        <x-button.filled-button href="{{ route('features.yolo') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="max-w-6xl mx-auto py-12 flex justify-center px-5 md:flex-row flex-col-reverse">
        <div class="flex flex-col p-5 items-center">
            <img src="{{ url('/images/guest/explore-map-interface.gif') }}" alt="Repay Mobile Explore Products and Properties Interface">
        </div>
        <div class="space-y-3 flex flex-col justify-center p-5 md:items-end md:text-end items-center text-center">
            <h1 class="text-4xl font-bold text-rp-neutral-700">Easily Find Products and<br>Properties Near You</h1>
            <p>RePay is your all-in-one marketplace wherein you can rent and sell your own products or properties
            </p>
            <x-button.filled-button href="{{ route('features.explore') }}" color="red" size="sm" class="w-[150px]">learn more</x-button.filled-button>
        </div>
    </div>


    <div class="max-w-6xl mx-auto py-12 flex md:flex-row flex-col justify-center ">
        <div class="space-y-3 flex flex-col justify-center p-5 md:items-start md:text-start items-center text-center">
            <h1 class="text-4xl font-bold text-rp-neutral-700">Never Miss Your<br>Bills Payment Again!</h1>
            <p>Automatically receive your invoices and get your bill notifications on time and on the dot.</p>
            {{-- <a href="{{ route('features.payments') }}"
                class="w-max uppercase bg-primary-500 hover:bg-primary-600 text-white font-bold px-4 py-2 text-sm rounded-lg">learn
                more</a> --}}
            <x-button.filled-button href="{{ route('features.payments') }}" size="sm" color="red" class="w-[150px]">learn
                more</x-button.filled-button>
        </div>
        <div class="flex flex-col gap-3 p-5 items-center" x-data="paymentTabs">
           
            <div class="">
                <img x-cloak x-show="billTab" src="{{ url('/images/guest/repay-pay-bill-interface.gif') }}" alt="Repay Mobile Pay bill Interface">
                <img x-cloak x-show="invoiceTab" src="{{ url('/images/guest/repay-invoice-interface.gif') }}" alt="Repay Mobile Invoice Interface">
                <img x-cloak x-show="transactionTab" src="{{ url('/images/guest/repay-payments-interface.gif') }}" alt="Repay Mobile Payments Interface">
            </div>

            <div class="flex flex-row gap-3 w-max scale-75 md:scale-100">
                <button @click="showBillTab" :class="classBillTab"
                    class="text-base uppercase px-6 py-2 rounded-lg font-medium">bills</button>
                <button @click="showInvoiceTab" :class="classInvoiceTab"
                    class="text-base uppercase px-6 py-2 rounded-lg font-medium">invoices</button>
                <button @click="showTransactionTab" :class="classTransactionTab"
                    class="text-base uppercase px-6 py-2 rounded-lg font-medium">transactions</button>
            </div>
        </div>
    </div>


    <div class="max-w-6xl mx-auto py-12 flex justify-center gap-3 md:flex-row flex-col-reverse">
        <div class="p-5 flex flex-col items-center px-5">
            <img src="{{ url('/images/guest/repay-view-property-and-product.png') }}" alt="Repay Mobile View Property and Product Interface">
        </div>
        <div
            class="p-5 space-y-3 flex flex-col justify-center md:items-end md:text-end items-center text-center">
            <h1 class="text-4xl font-bold text-rp-neutral-700">Instantly Sell Your Products and Properties</h1>
            <p>Lessen your paperworks and sell products or even properties with us!</p>
            <x-button.filled-button href="{{ route('features.assets') }}" size="sm" color="red" class="w-[150px]">learn
                more</x-button.filled-button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto py-12 flex justify-center gap-3 md:flex-row flex-col">
        <div
            class="basis-2/5 space-y-3 flex flex-col justify-center p-5 md:items-start md:text-start items-center text-center">
            <h1 class="text-4xl font-bold text-rp-neutral-700">Get Daily Rewards</h1>
            <p>Login everyday and get points in exchange for rewards!</p>
            <x-button.filled-button href="{{ route('features.yolo') }}" size="sm" color="red" class="w-[150px]">learn
                more</x-button.filled-button>
        </div>
        <div class="basis-3/5 p-5">
            <img src="{{ url('/images/guest/man-celebrating-rewards.png') }}" alt="A Man Celebrating Daily Rewards">
        </div>
    </div>

    <x-guest.download-app-section />
</div>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentTabs', () => ({
                billTab: true,
                invoiceTab: false,
                transactionTab: false,

                showBillTab() {
                    this.billTab = true;
                    this.invoiceTab = false;
                    this.transactionTab = false;
                },
                isBillTabInactive() {
                    return this.billTab === false;
                },
                classBillTab() {
                    return this.billTab ? 'bg-rp-neutral-800 text-white hover:bg-rp-neutral-900' : 'border-rp-neutral-800 text-rp-neutral-800 hover:opacity-60 border';
                },

                showInvoiceTab() {
                    this.billTab = false;
                    this.invoiceTab = true;
                    this.transactionTab = false;
                },
                isInvoiceTabInactive() {
                    return this.invoiceTab === false;
                },
                classInvoiceTab() {
                    return this.invoiceTab ? 'bg-rp-neutral-800 text-white hover:bg-rp-neutral-900' : 'border-rp-neutral-800 text-rp-neutral-800 hover:opacity-60 border';
                },

                showTransactionTab() {
                    this.billTab = false;
                    this.invoiceTab = false;
                    this.transactionTab = true;
                },
                isTransactionTabInactive() {
                    return this.transactionTab === false;
                },
                classTransactionTab() {
                    return this.transactionTab ? 'bg-rp-neutral-800 text-white hover:bg-rp-neutral-900' : 'border-rp-neutral-800 text-rp-neutral-800 hover:opacity-60 border';
                },
            }))
        })
    </script>
@endpush