@extends('adminlte::master')

@section('body')
<div id="app">
    <div class="container">
        <div class="wrapper" id="app">
            <div class="container">
                <!-- <vue-element-loading :active="spinnerData.loading" spinner="bar-fade-scale" color="#FF6700" /> -->
                <form id="cloudpayment-widget-form" class="card-form__inner">
                    <div class="row">
                        <div class="col-sm-12"
                            style="text-align: center;font-size: 14px;margin-bottom: 20px;font-weight: 600;">
                            <p style="line-height: 19px">Безопасность платежей обеспечивается банковской системой
                                Республики Казахстан и <a href="https://pitech.kz/"
                                    target="_blank">Pitech.kz</a>
                            </p>
                        </div>
                        <div class="col-sm-5">
                            <div class="card-input">
                                <label class="card-input__label">Имя</label>
                                <input type="text" class="card-input__input" value="{{ $payment->subscription->customer->name }}" disabled>
                            </div>
                            <div class="card-input">
                                <label class="card-input__label">Телефон абонента</label>
                                <the-mask :masked="false" mask="+# (###) ### ##-##-##" type="text"
                                    class="card-input__input" id="phone" value="{{ $payment->subscription->customer->phone }}" required disabled>
                                </the-mask>
                            </div>
                            {{--<div class="card-input">
                                <label class="card-input__label">E-mail *</label>
                                <input id="email" type="email" class="card-input__input" value="{{ $payment->subscription->customer->email }}" required>
                                <small class="form-text text-muted">На указанный e-mail будет отправлен чек об оплате</small>
                            </div>--}}
                        </div>
                        <div class="col-sm-1"></div>
                        <div class="col-sm-6">
                            <div class="card-input">
                                <label class="card-input__label">Услуга</label>
                                <input type="text" class="card-input__input" value="{{ $payment->subscription->product->title }}" disabled>
                            </div>
                            <div class="card-input">
                                <label class="card-input__label">{{ $payment->type == 'cloudpayments' ? 'Стоимость в месяц' : 'Стоимость' }}</label>
                                <input type="text" class="card-input__input" value="{{ $payment->amount }} тенге"
                                    disabled>
                            </div>
                        </div>
                        <button type="submit" class="card-form__button" style="font-size: 18px">
                            Перейти к оплате
                        </button>
                            <span id="loading" class="spinner-border spinner spinner-border-sm" style="color: #2364d2; width: 5rem; height: 5rem; display: none" role="status" aria-hidden="true"></span>
                        <p style="margin-top: 20px;text-align: center;">Нажимая "Перейти к оплате", Вы даёте согласие
                            на обработку Ваших персональных данных и принимаете
                            <a href="https://www.strela-academy.ru/offer.pdf"
                                style="color: #fc0000;text-decoration: underline;" target="_blank">Пользовательское
                                соглашение</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('adminlte_js')
<script>
$( "#cloudpayment-widget-form" ).submit(function( event ) {
    $('#loading').show();
    event.preventDefault();
    var data = JSON.parse('<?php echo $data; ?>');
    var settings = {
           "url": "https://cards-stage.pitech.kz/gw/payments/cards/charge",
        // бой
        // "url": "https://cards.pitech.kz/gw/payments/cards/charge",
        "method": "POST",
        "timeout": 0,
        "headers": {
            "Authorization": "Basic c2RJY2hNS3VTcVpza3BFOVdvVC1nSG9jSnhjd0xrbjY6WmxwYVJZTkFDbUJhR1Utc0RpRFEzUVM1RFhVWER0TzI=",
            //бой
           // "Authorization": "Basic NjBQWS1MWnluZGNQVl9LQzhjTm5tZW9oLTg2c2Y1MHA6VVA3WWxEa3pzZ3pYS2p2T2dMNjQxdEpOOFpnTUhEWXY=",
            "Content-Type": "application/json"
        },
        "data": JSON.stringify({
            "amount": data.amount,
            "currency": "KZT",
            "description": data.description,
            "extOrdersId": data.accountId,
            "errorReturnUrl": "https://www.strela-academy.ru/api/pitech/pay-fail",
            "successReturnUrl": "https://www.strela-academy.ru/thank-you",
            "callbackSuccessUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
            "callbackErrorUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
            "payload": {
                "phone": data.data.cloudPayments.customerReceipt.phone,
                "test": "yes",
                "location": "www.strela-academy.ru"
            },
            "extClientRef": data.customerId,
            "extOrdersTime": "1",
            "email": "",
            "shortenPaymentUrl": true,
            'saveCard': true,
            "fiscalization": true,
            "positions":[
                {
                    "count": 1,
                    "unitName": "pc",
                    "price": data.amount,
                    "name": data.description
                }
            ],
            "template": "blue"
        }),
    };

    console.log(data);
    // console.log(data.amount);
    // console.log(data.description);
    // console.log(data.accountId);
    // console.log(data.data.cloudPayments.customerReceipt.phone);

    $.ajax(settings).done(function (response) {
        $('#loading').hide();
        console.log(response.paymentUrl);
        window.location.href = response.paymentUrl;
    });


    {{--var widget = new cp.CloudPayments();--}}

    {{--var data = JSON.parse('<?php echo $data; ?>');--}}
    {{--//тут поставить параметр auth для отмены платежа--}}
    {{--widget.charge(data,--}}
    {{--function (options) { // success--}}
    {{--    window.location.href = "{{ route('cloudpayments.thank_you', [$payment->subscription->product->id]) }}";--}}
    {{--},--}}
    {{--function (reason, options) { // fail--}}
    {{--    window.location.href = "{{ route('cloudpayments.show_widget', [$payment->subscription->id]) }}";--}}
    {{--});--}}
});
</script>
@endsection

@section('adminlte_css')
<script src="https://widget.cloudpayments.ru/bundles/cloudpayments"></script>
<style>
@import url("https://fonts.googleapis.com/css?family=Source+Code+Pro:400,500,600,700|Source+Sans+Pro:400,600,700&display=swap");
form.errors :invalid {
  border-color: #dc3545;
}

.success-icon {
  background: url(/images/success.svg) no-repeat;
  display: block;
  width: 81px;
  height: 88px;
  margin: 0 auto 40px;
  background-position: center;
}

.error-icon {
  background: url(/images/error.svg) no-repeat;
  display: block;
  width: 81px;
  height: 88px;
  margin: 0 auto 40px;
}

body {
  background: #ddeefc;
  font-family: "Source Sans Pro", sans-serif;
  font-size: 16px;
}

* {
  box-sizing: border-box;
}
*:focus {
  outline: none;
}

.wrapper {
  /* min-height: 100vh; */
  display: flex;
  padding: 50px 15px;
}
@media screen and (max-width: 700px), (max-height: 500px) {
  .wrapper {
    flex-wrap: wrap;
    flex-direction: column;
  }
}

.card-form {
  max-width: 570px;
  margin: auto;
  width: 100%;
}
@media screen and (max-width: 576px) {
  .card-form {
    margin: 0 auto;
  }
}
.spinner {
    position: fixed;
    z-index: 999;
    margin: auto;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
}
.card-form__inner {
  background: #fff;
  box-shadow: 0 30px 60px 0 rgba(90, 116, 148, 0.4);
  border-radius: 10px;
  padding: 35px;
}
@media screen and (max-width: 480px) {
  .card-form__inner {
    padding: 25px;
  }
}
@media screen and (max-width: 360px) {
  .card-form__inner {
    padding: 15px;
  }
}
.card-form__row {
  display: flex;
  align-items: flex-start;
}
@media screen and (max-width: 480px) {
  .card-form__row {
    flex-wrap: wrap;
  }
}
.card-form__col {
  flex: auto;
  margin-right: 35px;
}
.card-form__col:last-child {
  margin-right: 0;
}
@media screen and (max-width: 480px) {
  .card-form__col {
    margin-right: 0;
    flex: unset;
    width: 100%;
    margin-bottom: 20px;
  }
  .card-form__col:last-child {
    margin-bottom: 0;
  }
}
.card-form__col.-cvv {
  max-width: 150px;
}
@media screen and (max-width: 480px) {
  .card-form__col.-cvv {
    max-width: initial;
  }
}
.card-form__group {
  display: flex;
  align-items: flex-start;
  flex-wrap: wrap;
}
.card-form__group .card-input__input {
  flex: 1;
  margin-right: 15px;
}
.card-form__group .card-input__input:last-child {
  margin-right: 0;
}
.card-form__button {
  width: 100%;
  height: 55px;
  background: #2364d2;
  border: none;
  border-radius: 5px;
  font-size: 22px;
  font-weight: 500;
  font-family: "Source Sans Pro", sans-serif;
  box-shadow: 3px 10px 20px 0px rgba(35, 100, 210, 0.3);
  color: #fff;
  margin-top: 20px;
  cursor: pointer;
}
@media screen and (max-width: 480px) {
  .card-form__button {
    margin-top: 10px;
  }
}

.card-item {
  max-width: 430px;
  height: 270px;
  margin-left: auto;
  margin-right: auto;
  position: relative;
  z-index: 2;
  width: 100%;
}
@media screen and (max-width: 480px) {
  .card-item {
    max-width: 310px;
    height: 220px;
    width: 90%;
  }
}
@media screen and (max-width: 360px) {
  .card-item {
    height: 180px;
  }
}
.card-item.-active .card-item__side.-front {
  transform: perspective(1000px) rotateY(180deg) rotateX(0deg) rotateZ(0deg);
}
.card-item.-active .card-item__side.-back {
  transform: perspective(1000px) rotateY(0) rotateX(0deg) rotateZ(0deg);
}
.card-item__focus {
  position: absolute;
  z-index: 3;
  border-radius: 5px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  transition: all 0.35s cubic-bezier(0.71, 0.03, 0.56, 0.85);
  opacity: 0;
  pointer-events: none;
  overflow: hidden;
  border: 2px solid rgba(255, 255, 255, 0.65);
}
.card-item__focus:after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  background: #08142f;
  height: 100%;
  border-radius: 5px;
  filter: blur(25px);
  opacity: 0.5;
}
.card-item__focus.-active {
  opacity: 1;
}
.card-item__side {
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 20px 60px 0 rgba(14, 42, 90, 0.55);
  transform: perspective(2000px) rotateY(0deg) rotateX(0deg) rotate(0deg);
  transform-style: preserve-3d;
  transition: all 0.8s cubic-bezier(0.71, 0.03, 0.56, 0.85);
  backface-visibility: hidden;
  height: 100%;
}
.card-item__side.-back {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  transform: perspective(2000px) rotateY(-180deg) rotateX(0deg) rotate(0deg);
  z-index: 2;
  padding: 0;
  height: 100%;
}
.card-item__side.-back .card-item__cover {
  transform: rotateY(-180deg);
}
.card-item__bg {
  max-width: 100%;
  display: block;
  max-height: 100%;
  height: 100%;
  width: 100%;
  object-fit: cover;
}
.card-item__cover {
  height: 100%;
  background-color: #1c1d27;
  position: absolute;
  height: 100%;
  background-color: #1c1d27;
  left: 0;
  top: 0;
  width: 100%;
  border-radius: 15px;
  overflow: hidden;
}
.card-item__cover:after {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(6, 2, 29, 0.45);
}
.card-item__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 40px;
  padding: 0 10px;
}
@media screen and (max-width: 480px) {
  .card-item__top {
    margin-bottom: 25px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__top {
    margin-bottom: 15px;
  }
}
.card-item__chip {
  width: 60px;
}
@media screen and (max-width: 480px) {
  .card-item__chip {
    width: 50px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__chip {
    width: 40px;
  }
}
.card-item__type {
  height: 45px;
  position: relative;
  display: flex;
  justify-content: flex-end;
  max-width: 100px;
  margin-left: auto;
  width: 100%;
}
@media screen and (max-width: 480px) {
  .card-item__type {
    height: 40px;
    max-width: 90px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__type {
    height: 30px;
  }
}
.card-item__typeImg {
  max-width: 100%;
  object-fit: contain;
  max-height: 100%;
  object-position: top right;
}
.card-item__info {
  color: #fff;
  width: 100%;
  max-width: calc(100% - 85px);
  padding: 10px 15px;
  font-weight: 500;
  display: block;
  cursor: pointer;
}
@media screen and (max-width: 480px) {
  .card-item__info {
    padding: 10px;
  }
}
.card-item__holder {
  opacity: 0.7;
  font-size: 13px;
  margin-bottom: 6px;
}
@media screen and (max-width: 480px) {
  .card-item__holder {
    font-size: 12px;
    margin-bottom: 5px;
  }
}
.card-item__wrapper {
  font-family: "Source Code Pro", monospace;
  padding: 25px 15px;
  position: relative;
  z-index: 4;
  height: 100%;
  text-shadow: 7px 6px 10px rgba(14, 42, 90, 0.8);
  user-select: none;
}
@media screen and (max-width: 480px) {
  .card-item__wrapper {
    padding: 20px 10px;
  }
}
.card-item__name {
  font-size: 18px;
  line-height: 1;
  white-space: nowrap;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  text-transform: uppercase;
}
@media screen and (max-width: 480px) {
  .card-item__name {
    font-size: 16px;
  }
}
.card-item__nameItem {
  display: inline-block;
  min-width: 8px;
  position: relative;
}
.card-item__number {
  font-weight: 500;
  line-height: 1;
  color: #fff;
  font-size: 27px;
  margin-bottom: 35px;
  display: inline-block;
  padding: 10px 15px;
  cursor: pointer;
}
@media screen and (max-width: 480px) {
  .card-item__number {
    font-size: 21px;
    margin-bottom: 15px;
    padding: 10px 10px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__number {
    font-size: 19px;
    margin-bottom: 10px;
    padding: 10px 10px;
  }
}
.card-item__numberItem {
  width: 16px;
  display: inline-block;
}
.card-item__numberItem.-active {
  width: 30px;
}
@media screen and (max-width: 480px) {
  .card-item__numberItem {
    width: 13px;
  }
  .card-item__numberItem.-active {
    width: 16px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__numberItem {
    width: 12px;
  }
  .card-item__numberItem.-active {
    width: 8px;
  }
}
.card-item__content {
  color: #fff;
  display: flex;
  align-items: flex-start;
}
.card-item__date {
  flex-wrap: wrap;
  font-size: 18px;
  margin-left: auto;
  padding: 10px;
  display: inline-flex;
  width: 80px;
  white-space: nowrap;
  flex-shrink: 0;
  cursor: pointer;
}
@media screen and (max-width: 480px) {
  .card-item__date {
    font-size: 16px;
  }
}
.card-item__dateItem {
  position: relative;
}
.card-item__dateItem span {
  width: 22px;
  display: inline-block;
}
.card-item__dateTitle {
  opacity: 0.7;
  font-size: 13px;
  padding-bottom: 6px;
  width: 100%;
}
@media screen and (max-width: 480px) {
  .card-item__dateTitle {
    font-size: 12px;
    padding-bottom: 5px;
  }
}
.card-item__band {
  background: rgba(0, 0, 19, 0.8);
  width: 100%;
  height: 50px;
  margin-top: 30px;
  position: relative;
  z-index: 2;
}
@media screen and (max-width: 480px) {
  .card-item__band {
    margin-top: 20px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__band {
    height: 40px;
    margin-top: 10px;
  }
}
.card-item__cvv {
  text-align: right;
  position: relative;
  z-index: 2;
  padding: 15px;
}
.card-item__cvv .card-item__type {
  opacity: 0.7;
}
@media screen and (max-width: 360px) {
  .card-item__cvv {
    padding: 10px 15px;
  }
}
.card-item__cvvTitle {
  padding-right: 10px;
  font-size: 15px;
  font-weight: 500;
  color: #fff;
  margin-bottom: 5px;
}
.card-item__cvvBand {
  height: 45px;
  background: #fff;
  margin-bottom: 30px;
  text-align: right;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 10px;
  color: #1a3b5d;
  font-size: 18px;
  border-radius: 4px;
  box-shadow: 0px 10px 20px -7px rgba(32, 56, 117, 0.35);
}
@media screen and (max-width: 480px) {
  .card-item__cvvBand {
    height: 40px;
    margin-bottom: 20px;
  }
}
@media screen and (max-width: 360px) {
  .card-item__cvvBand {
    margin-bottom: 15px;
  }
}

.card-list {
  margin-bottom: -130px;
}
@media screen and (max-width: 480px) {
  .card-list {
    margin-bottom: -120px;
  }
}

.card-input {
  max-width: 100%;
  margin-bottom: 20px;
}
.card-input__label {
  font-size: 14px;
  margin-bottom: 5px;
  font-weight: 500;
  color: #1a3b5d;
  width: 100%;
  display: block;
  user-select: none;
}
.card-input__input {
  width: 100%;
  height: 50px;
  border-radius: 5px;
  box-shadow: none;
  border: 1px solid #ced6e0;
  transition: all 0.3s ease-in-out;
  font-size: 18px;
  padding: 5px 15px;
  background: none;
  color: #1a3b5d;
  font-family: "Source Sans Pro", sans-serif;
}
.card-input__input:hover, .card-input__input:focus {
  border-color: #3d9cff;
}
.card-input__input:focus {
  box-shadow: 0px 10px 20px -13px rgba(32, 56, 117, 0.35);
}
.card-input__input.-select {
  -webkit-appearance: none;
  background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAeCAYAAABuUU38AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAUxJREFUeNrM1sEJwkAQBdCsngXPHsQO9O5FS7AAMVYgdqAd2IGCDWgFnryLFQiCZ8EGnJUNimiyM/tnk4HNEAg/8y6ZmMRVqz9eUJvRaSbvutCZ347bXVJy/ZnvTmdJ862Me+hAbZCTs6GHpyUi1tTSvPnqTpoWZPUa7W7ncT3vK4h4zVejy8QzM3WhVUO8ykI6jOxoGA4ig3BLHcNFSCGqGAkig2yqgpEiMsjSfY9LxYQg7L6r0X6wS29YJiYQYecemY+wHrXD1+bklGhpAhBDeu/JfIVGxaAQ9sb8CI+CQSJ+QmJg0Ii/EE2MBiIXooHRQhRCkBhNhBcEhLkwf05ZCG8ICCOpk0MULmvDSY2M8UawIRExLIQIEgHDRoghihgRIgiigBEjgiFATBACAgFgghEwSAAGgoBCBBgYAg5hYKAIFYgHBo6w9RRgAFfy160QuV8NAAAAAElFTkSuQmCC");
  background-size: 12px;
  background-position: 90% center;
  background-repeat: no-repeat;
  padding-right: 30px;
}

.slide-fade-up-enter-active {
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
  position: relative;
}

.slide-fade-up-leave-active {
  transition: all 0.25s ease-in-out;
  position: absolute;
}

.slide-fade-up-enter {
  opacity: 0;
  transform: translateY(15px);
  pointer-events: none;
}

.slide-fade-up-leave-to {
  opacity: 0;
  transform: translateY(-15px);
  pointer-events: none;
}

.slide-fade-right-enter-active {
  transition: all 0.25s ease-in-out;
  transition-delay: 0.1s;
  position: relative;
}

.slide-fade-right-leave-active {
  transition: all 0.25s ease-in-out;
  position: absolute;
}

.slide-fade-right-enter {
  opacity: 0;
  transform: translateX(10px) rotate(45deg);
  pointer-events: none;
}

.slide-fade-right-leave-to {
  opacity: 0;
  transform: translateX(-10px) rotate(45deg);
  pointer-events: none;
}

.github-btn {
  position: absolute;
  right: 40px;
  bottom: 50px;
  text-decoration: none;
  padding: 15px 25px;
  border-radius: 4px;
  box-shadow: 0px 4px 30px -6px rgba(36, 52, 70, 0.65);
  background: #24292e;
  color: #fff;
  font-weight: bold;
  letter-spacing: 1px;
  font-size: 16px;
  text-align: center;
  transition: all 0.3s ease-in-out;
}
@media screen and (min-width: 500px) {
  .github-btn:hover {
    transform: scale(1.1);
    box-shadow: 0px 17px 20px -6px rgba(36, 52, 70, 0.36);
  }
}
@media screen and (max-width: 700px) {
  .github-btn {
    position: relative;
    bottom: auto;
    right: auto;
    margin-top: 20px;
  }
  .github-btn:active {
    transform: scale(1.1);
    box-shadow: 0px 17px 20px -6px rgba(36, 52, 70, 0.36);
  }
}

input[type=text]:disabled {
  background: #f8f8f8;
}
</style>
@endsection
