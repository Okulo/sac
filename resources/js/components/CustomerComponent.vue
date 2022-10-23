<template>
    <div>
        <b-modal ref="modal-customer" hide-footer @hidden="closeModal()" no-fade size="xl" title="Карточка клиента" class="modal bd-example-modal-lg" :id="'modal-customer-' + type">
            <!-- <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content"> -->
            <!-- <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Карточка клиента</h5>
                <button type="button" data-dismiss="modal" class="close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div> -->
            <pulse-loader style="z-index: 999; background: rgba(0, 0, 0, 0.19); height: 100%; line-height: 100vh;" class="spinner" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>
            <div class="card" style="padding-bottom: 0px">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label for="name" class="col-sm-4 col-form-label">Имя</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="name" v-model="customer.name" name="customer.name" required>
                            </div>
                            <label for="phone" class="col-sm-4 col-form-label">Телефон</label>
                            <div class="col-sm-8">
                                <the-mask
                                    :masked="false"
                                    mask="+# (###) ### ##-##-##"
                                    type="text"
                                    class="form-control"
                                    id="phone"
                                    v-model="customer.phone"
                                    name="customer.phone"
                                    required
                                ></the-mask>
                            </div>
                            <label for="email" class="col-sm-4 col-form-label">E-mail</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="email" v-model="customer.email" name="customer.email">
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="comments" class="col-sm-4 col-form-label">Примечания</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" v-model="customer.comments" name="customer.comments" id="" cols="30" rows="10"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bd-example">
                <b-card no-body>
                    <b-tabs card>
                        <!-- Render Tabs, supply a unique `key` to each tab -->
                        <b-tab v-for="(subscription, subIndex) in subscriptions" :key="subIndex" :active="showTab(subIndex)" :title="getSubscriptionTitle(subscription.product_id)" style="position: relative;">
                            <div v-if="isDisabled(subscription)" style="width: 100%; height: 100%; display: block; background: #0000001a; z-index: 10000; top: 0; position: absolute; left: 0;"></div>
                            <div class="row">
                                <button
                                    type="button"
                                    title="Удалить услугу"
                                    @click="removeProduct(subscription.id, subIndex)"
                                    class="close"
                                    data-dismiss="alert"
                                    aria-label="Close"
                                    style="right: 20px; position: absolute; z-index: 1"
                                ><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="row">
                                <div class="form-group col-12">
                                    <div v-for="user_name, index in users" v-if="index == subscription.user_id">
                                        Оператор:<b> {{ user_name}}</b>
                                        &nbsp &nbsp  <a v-if="userRole != 'operator'" :href="'/users/change/'+subscription.id" type="button" title="Изменить" class="btn btn-sm "><i data-v-754b2df6="" class="fa fa-edit"></i></a>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="product_id" class="col-form-label">Услуга</label>
                                    <select v-if="! subscription.id" v-model="subscription.product_id" :name="'subscriptions.' + subIndex + '.product_id'" id="product-id" class="col-sm-10 form-control">
                                        <option v-for="(option, optionIndex) in products" :key="optionIndex" :value="optionIndex" >{{ option.title }}</option>
                                    </select>
                                    <input v-else :value="getSubscriptionTitle(subscription.product_id)" id="product_id" class="col-sm-10 form-control" type="text" disabled>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="price" class="col-form-label">Цена</label>
                                    <select v-model="subscription.price" :name="'subscriptions.' + subIndex + '.price'" id="price" class="col-sm-10 form-control" @change="selectPrice($event)" :disabled="isDisabled(subscription)">
                                        <option v-if="subscription.price != null" :value="subscription.price" selected>{{ subscription.price }}</option>
                                        <option v-for="(option, optionIndex) in getPrices(subscription.product_id)" :key="optionIndex" :value="option" v-if="option != subscription.price">{{ option }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <label for="payment_type" class="col-form-label">Тип оплаты</label>
                                    <select v-model="subscription.payment_type" :name="'subscriptions.' + subIndex + '.payment_type'" id="payment_type" class="col-sm-10 form-control" :disabled="isDisabled(subscription)">
                                        <option v-for="(option, optionIndex) in getPaymentTypes(subscription.product_id)" :key="optionIndex" :value="optionIndex">{{ option.title }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="status" class="col-form-label">Статус абонемента</label>
                                    <select v-model="subscription.status" :name="'subscriptions.' + subIndex + '.status'" id="status" class="col-sm-10 form-control" :disabled="isDisabled(subscription)">
                                        <option v-for="(option, optionIndex) in getStatuses(subscription.product_id, subscription.payment_type)" :key="optionIndex" :value="optionIndex">{{ option }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6" v-if="subscription.payment_type != 'simple_payment'">
                                    <label for="started_at" class="col-form-label">Дата старта</label>
                                    <datetime
                                        :name="'subscriptions.' + subIndex + '.started_at'"
                                        type="date"
                                        v-model="subscription.started_at"
                                        input-class="col-sm-10 my-class form-control"
                                        valueZone="Asia/Almaty"
                                        value-zone="Asia/Almaty"
                                        zone="Asia/Almaty"
                                        format="dd LLLL"
                                        :auto="true"
                                        :disabled="isDisabled(subscription)"
                                    ></datetime>
                                </div>
                                <div class="form-group col-sm-6" v-if="subscription.payment_type != 'simple_payment'">
                                    <label for="tries_at" class="col-form-label">Дата окончания абонемента и следующего платежа</label>
                                    <div v-if="subscription.payment_type == 'tries' && subscription.status != 'trial'">
                                        <div v-show="!subscription.is_edit_ended_at">
                                            <span class="ended_at-span">{{ showDate(subscription.tries_at) }}</span>
                                            <button class="btn btn-info" @click="subscription.is_edit_ended_at = !subscription.is_edit_ended_at" :disabled="isDisabled(subscription)">Изменить</button>
                                        </div>
                                        <datetime
                                            v-show="subscription.is_edit_ended_at"
                                            :name="'subscriptions.' + subIndex + '.tries_at'"
                                            type="date"
                                            v-model="subscription.tries_at"
                                            input-class="col-sm-10 my-class form-control"
                                            valueZone="Asia/Almaty"
                                            value-zone="Asia/Almaty"
                                            zone="Asia/Almaty"
                                            format="dd LLLL"
                                            :auto="true"
                                            :disabled="isDisabled(subscription)"
                                        ></datetime>
                                    </div>
                                    <div v-if="subscription.payment_type == 'tries' && subscription.status == 'trial'">
                                        <div v-show="!subscription.is_edit_ended_at">
                                            <span class="ended_at-span">{{ showDateWithTrial(subscription.started_at, products[subscription.product_id].trial_period) }}</span>
                                            <button class="btn btn-info" @click="subscription.is_edit_ended_at = !subscription.is_edit_ended_at" :disabled="isDisabled(subscription)">Изменить</button>
                                        </div>
                                        <datetime
                                            v-show="subscription.is_edit_ended_at"
                                            :name="'subscriptions.' + subIndex + '.tries_at'"
                                            type="date"
                                            v-model="subscription.tries_at"
                                            input-class="col-sm-10 my-class form-control"
                                            valueZone="Asia/Almaty"
                                            value-zone="Asia/Almaty"
                                            zone="Asia/Almaty"
                                            format="dd LLLL"
                                            :auto="true"
                                            :disabled="isDisabled(subscription)"
                                        ></datetime>
                                    </div>
                                    <div v-else-if="subscription.payment_type == 'transfer' || subscription.payment_type == 'cloudpayments' || subscription.payment_type == 'pitech'">
                                        <div v-show="!subscription.is_edit_ended_at">
                                            <span class="ended_at-span">{{ showDate(subscription.ended_at) }}</span>
                                            <button class="btn btn-info" @click="subscription.is_edit_ended_at = !subscription.is_edit_ended_at" :disabled="isDisabled(subscription)">Изменить</button>
                                        </div>
                                        <datetime
                                            v-show="subscription.is_edit_ended_at"
                                            :name="'subscriptions.' + subIndex + '.ended_at'"
                                            type="date"
                                            v-model="subscription.ended_at"
                                            input-class="col-sm-10 my-class form-control"
                                            valueZone="Asia/Almaty"
                                            value-zone="Asia/Almaty"
                                            zone="Asia/Almaty"
                                            format="dd LLLL"
                                            :auto="true"
                                            :disabled="isDisabled(subscription)"
                                        ></datetime>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 15px">
                                <div v-if="subscription.status == 'refused'" class="form-group col-sm-6">
                                    <label for="reason_id" class="col-form-label">Причина отказа</label>
                                    <select v-model="subscription.reason_id" :name="'subscriptions.' + subIndex + '.reason_id'" id="reason_id" class="col-sm-10 form-control" :disabled="isDisabled(subscription)">
                                        <option v-for="(reason, reasonIndex) in subscription.reasons" :key="reasonIndex" :value="reason.id">{{ reason.title }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-6" v-if="userRole != 'operator' || userTeamIds.length > 1">
                                    <label for="team_id" class="col-form-label">Команда</label>
                                    <select v-model="subscription.team_id" :name="'subscriptions.' + subIndex + '.team_id'" id="team_id" class="col-sm-10 form-control" :disabled="isDisabled(subscription)">
                                        <option v-for="(team, teamIndex) in teamsProp" :key="teamIndex" :value="team.id">{{ team.name }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-6" id="change-price" v-if="currentPrice && subscription.status != 'trial' && subscription.payment_type == 'cloudpayments'">
                                    <hr>
                                    <label class="col-form-label">Изменить цену подписки</label>

                                    <div class="form-inline">
                                        <div class="form-group mb-2" style="margin-right: 45px">
                                            {{currentPrice}}
                                        </div>
                                        <button @click="changeAmount(subscription.cp_subscription_id, subscription.id, subscription.product.title)" class="btn btn-warning mb-2">Изменить</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 15px" v-if="subscription.payment_type == 'cloudpayments' && type == 'edit' && subscription.cp_subscription_id != null &&  customer.cards">
                                <div v-for="(card,index) in customer.cards">

                                    <div v-if="index == customer.cards.length - 1">
                                        <div class="form-group col-sm-12" v-if="card.cp_account_id == subscription.id && subscription.status != 'paid' && card.type != 'pitech'">
                                            <button type="button" class="btn btn-dark" :id="'subscription-' + subscription.id" @click="manualWriteOffPayment(subscription.id, card.id)">Ручное списание</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 15px" v-if="subscription.payment_type == 'transfer'">
                                <div class="col-sm-6">
                                    <button  class="btn btn-primary" @click="showTransferModal(subscription.id)" :disabled="isDisabled(subscription)">Загрузить чек</button>
                                </div>
                                <b-modal size="lg" hide-footer title="Загрузить чек" :id="'modalTransfer-' + subscription.id" class="modal">
                                    <!-- <div class="modal-dialog modal-dialog-centered modal-lg"> -->
                                    <!-- <div class="modal-content"> -->
                                    <!-- <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Загрузить чек</h5>
                                        <button type="button" class="close" @click="hideTransferModal(subscription.id)">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div> -->
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label for="check" class="col-form-label">Оплачен от</label>
                                                <datetime
                                                    :name="'subscriptions.' + subIndex + '.newPayment.from'"
                                                    type="date"
                                                    v-model="subscription.newPayment.from"
                                                    input-class="col-sm-10 my-class form-control"
                                                    valueZone="Asia/Almaty"
                                                    value-zone="Asia/Almaty"
                                                    zone="Asia/Almaty"
                                                    format="dd LLLL"
                                                    :auto="true"
                                                ></datetime>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label for="check" class="col-form-label">До</label>
                                                <datetime
                                                    :name="'subscriptions.' + subIndex + '.newPayment.from'"
                                                    type="date"
                                                    v-model="subscription.newPayment.to"
                                                    input-class="col-sm-10 my-class form-control"
                                                    valueZone="Asia/Almaty"
                                                    value-zone="Asia/Almaty"
                                                    zone="Asia/Almaty"
                                                    format="dd LLLL"
                                                    :auto="true"
                                                ></datetime>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label for="check" class="col-form-label">Загрузить чек</label>
                                                <upload-file :value-prop="subscription.newPayment.check" @file='setFileToSubscription($event, subIndex)'></upload-file>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label for="quantity" class="col-form-label">Период</label>
                                                <select v-model="subscription.newPayment.quantity" name="quantity" id="quantity" class="col-sm-10 form-control">
                                                    <option v-for="(option, optionIndex) in quantities" :key="optionIndex" :value="optionIndex">{{ option }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <footer class="modal-footer">
                                        <button @click="hideTransferModal(subscription.id, subscription.newPayment.to, subIndex)" type="button" class="btn btn-primary">Сохранить</button>
                                        <!-- <button @click="submit()" type="button" class="btn btn-primary">Сохранить</button> -->
                                    </footer>
                                    <!-- </div>
                                </div> -->
                                </b-modal>
                            </div>
                            <div class="row" v-if="customer.card && (customer.card.type != 'pitech') && (subscription.payment_type == 'simple_payment') && subscription.status != 'paid'" style="margin-bottom: 15px">
                                <div class="col-sm-12">
                                    <span><span style="font-weight: bold">{{ customer.card.type }}</span> (конец карты - {{ customer.card.last_four }}) </span>
                                    <button type="button" class="btn btn-dark" :id="'writeOffPaymentByToken-' + subscription.id" @click="writeOffPaymentByToken(subscription.id, customer.card.id)" :disabled="isDisabled(subscription)">Списать оплату с привязанной карты</button>
                                </div>
                            </div>
                            <div class="row" v-if="subscription.recurrent && (subscription.payment_type == 'cloudpayments' || subscription.payment_type == 'simple_payment')" style="margin-bottom: 15px">
                                <div class="col-sm-6">
                                    <div class="recurrent_block">
                                        <img style="margin-right: 20px" src="/images/cp.png" alt="pitech" width="30px" />
                                        <a target="_blank" :href="subscription.recurrent.link">{{ subscription.recurrent.link }}</a>
                                        <input type="hidden" :id="'recurrent-link-' + subIndex" :value="subscription.recurrent.link">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="recurrent_button-block">
                                        <button class="btn btn-info" @click="copyRecurrentLink(subIndex)">Копировать</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-if="subscription.recurrent && (subscription.payment_type == 'pitech' || subscription.payment_type == 'simple_payment')" style="margin-bottom: 15px">
                                <div class="col-sm-6">
                                    <div class="recurrent_block">

                                        <img style="margin-right: 20px" src="/images/pitech1.png" alt="pitech" width="30px" />
                                        <a target="_blank" :href="baseUrl+'/pitech/'+subscription.id">{{baseUrl}}/pitech/{{ subscription.id }}</a>
                                        <input type="hidden" :id="'pitech-link-' + subIndex" :value="baseUrl+'/pitech/'+subscription.id">


                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="recurrent_button-block">
                                        <button class="btn btn-info" @click="copyPitechLink(subIndex)">Копировать</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-if="(subscription.status == 'trial' && subscription.id) && (!customer.cards || customer.cards.length == 0)" style="margin-bottom: 15px">
                                <div class="col-sm-6">
                                    <div class="recurrent_block">
                                        <span class="cardLink">
                                            <div id="genBtn">  Привязка карты на сумму - <input :value="getProductBlockAmount(subscription.product_id)" style="width: 60px" type="number"  disabled="true"> &nbsp &nbsp &nbsp &nbsp &nbsp
                                            <button  class="btn btn-outline-success" @click="genlink(subscription.id)">Генерировать ссылку</button><p></p>
                                        </div>
                                        <img id="pitechlogo" style="margin-right: 20px; display: none" src="/images/pitech1.png" alt="pitech" width="30px" />
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="recurrent_button-block">
                                        <button class="btn btn-info" @click="copyGenLink(subIndex)">Копировать</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin-bottom: 15px" >

                                <div v-if="(customer.cards) " class="form-group col-sm-6">
                                    <div v-for="card in customer.cards">
                                        <div class="col-sm-12" v-if="card.type == 'pitech' && (subscription.payment_type == 'simple_payment') && subscription.status != 'paid'">
                                            <span><span style="font-weight: bold">{{ card.type }}</span></span>
                                            <button type="button" class="btn btn-dark" :id="'writeOffPaymentByToken-' + subscription.id" @click="paymentByPitechCard(subscription.id, card.id)" :disabled="isDisabled(subscription)">Списать оплату с привязанной карты</button>
                                        </div>
                                        <button v-if="(card.type == 'pitech' && card.cp_account_id == subscription.id) && (subscription.payment_type == 'pitech' || subscription.payment_type == 'simple_payment')" type="button" class="btn btn-outline-info" :id="'subscription-' + subscription.id" @click="manualPitech(customerId, subscription.id, subscription.product.title, subscription.price)">Ручное списание с карты Pitech</button>
                                    </div>
                                </div>

                            </div>
                            <br><br>
                            <div v-show="type == 'edit'" class="row" style="margin-bottom: 15px;">
                                <div class="col-sm-12">
                                    <a target="_blank" :href="'/userlogs?subscription_id=' + subscription.id">Логи абонемента</a>
                                    <span> | </span>
                                    <a style="color: #3490dc;cursor: pointer" @click="showHistorySubscriptionModal(subscription.id)">История абонемента</a>
                                    <div :id="'modalHistorySubscription-' + subscription.id" class="modal">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLongTitle">История абонемента</h5>
                                                    <button type="button" class="close" @click="hideHistorySubscriptionModal(subscription.id)">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <vc-calendar
                                                            :min-date='new Date(subscription.started_at)'
                                                            :max-date='new Date(subscription.ended_at)'
                                                            :columns="$screens({ default: 1, lg: 2 })"
                                                            :rows="$screens({ default: 1, lg: 2 })"
                                                            :is-expanded="$screens({ default: true, lg: true })"
                                                            :attributes='subscription.history'
                                                        />

                                                        <div style="width: 100%; margin-top: 20px; text-align: center">
                                                            <p><span style="color: green">Зеленый</span> - заморозка</p>
                                                            <p><span style="color: red">Красный</span> - перевод</p>
                                                            <p><span style="color: yellow">Желтый</span> - подписка</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <footer class="modal-footer">
                                                    <button @click="hideHistorySubscriptionModal(subscription.id)" type="button" class="btn btn-dark">Выйти</button>
                                                    <!-- <button @click="submit()" type="button" class="btn btn-primary">Сохранить</button> -->
                                                </footer>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <ul class="list-group">
                                        <li class="list-group-item list-payment-Completed" v-for="item in subscriptionLogs"
                                            v-if="item.type.value == 'Изменена стоимость подписки' && item.subscription_id.value == subscription.id">
                                            <div v-if="item.type.value == 'Изменена стоимость подписки'">
                                                <a href="#">ID: {{ item.id.value }}</a>
                                                <span> | </span>
                                                {{ item.created_at.value}}, изменена стоимость подписки
                                                <span> | </span>
                                                <span v-html="item.data.value"></span>
                                            </div>
                                        </li>
                                        <li class="list-group-item list-payment-Completed" v-for="item in subscriptionLogs"
                                            v-if="item.type.value == 'Ошибка при сохранении карты' && item.subscription_id.value == subscription.id">
                                            <div v-if="item.type.value == 'Ошибка при сохранении карты'">
                                                <a href="#">ID: {{ item.id.value }}</a>
                                                <span> | </span>
                                                {{ item.created_at.value}}, Ошибка при сохранении карты
                                                <span> | </span>
                                                <span v-html="item.data.value"></span>
                                            </div>
                                        </li>
                                        <li class="list-group-item list-payment-Completed" v-for="item in subscriptionLogs"
                                            v-if="item.type.value == 'Карта успешно привязана' && item.subscription_id.value == subscription.id">
                                            <div v-if="item.type.value == 'Карта успешно привязана'">
                                                <a href="#">ID: {{ item.id.value }}</a>
                                                <span> | </span>
                                                {{ item.created_at.value}}, Карта успешно привязана
                                                <span> | </span>
                                                <span v-html="item.data.value"></span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                &nbsp;
                                <div class="col-sm-12">
                                    <ul class="list-group">
                                        <li :class="'list-group-item list-payment-' + payment.status" v-for="(payment, paymentIndex) in subscription.payments" :key="paymentIndex">
                                            <a :href="payment.url" target="_blank">ID: {{ payment.id }}</a>
                                            <span> | </span>
                                            {{ payment.title }}
                                            <!--                                            <span v-if="payment.type == 'pitech' && payment.status == 'Completed'" >Оплачено через Pitech</span>-->
                                            <a v-if="payment.type == 'transfer' && payment.status == 'Completed'" target="_blank" :href="payment.check">(чек оплаты)</a>
                                            <span> | </span>
                                            <a :href="payment.user.url" target="_blank">{{ payment.user.name }}</a>
                                            <span class="list-group-remove" @click="deletePayment(payment, paymentIndex, subIndex)">X</span>
                                            <a :href="payment.edit" target="_blank" class="list-group-remove" style="
                                                        margin-right: 5px;
                                                        background-color: #abab00;
                                                    ">E</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </b-tab>
                        <template #tabs-end>
                            <b-nav-item style="background: #6cb2eb; border-radius: 5px;" role="presentation" @click.prevent="addProduct()" title="Добавить услугу" href="#"><b style="color: #ffffff;">+</b></b-nav-item>
                        </template>
                    </b-tabs>
                </b-card>
            </div>
            <footer class="modal-footer">
                <button @click="closeModal()" type="button" class="btn btn-secondary">Отмена</button>
                <button @click="submit()" type="button" class="btn btn-primary">Сохранить</button>
            </footer>
            <!-- </div>
        </div> -->
        </b-modal>
    </div>
</template>
<script>
    import moment from 'moment';
    import {TheMask} from 'vue-the-mask'
    import {mask} from 'vue-the-mask'
    import DatePicker from 'v-calendar/lib/components/date-picker.umd'
    import Calendar from 'v-calendar/lib/components/calendar.umd'

    export default {
        components: {
            TheMask,
            Calendar,
            DatePicker
        },
        directives: {mask},
        props: [
            'typeProp',
            'nameProp',
            'customerIdProp',
            'subscriptionIdProp',
            'userIdProp',
        ],
        data() {
            return {
                teamsProp: [],
                baseUrl: window.location.origin,
                userTeamIds: [],
                userRole: 'operator',
                subscriptionId: this.subscriptionIdProp,
                type: this.typeProp,
                name: this.nameProp,
                spinnerData: {
                    loading: false,
                    color: '#6cb2eb',
                    size: '100px',
                },
                customer: {
                    name: '',
                    phone: '',
                    email: '',
                    comments: '',
                },
                currentPrice: '',
                products: {},
                subscriptions: [],
                users: [],
                subscriptionLogs: {},
                paymentTypes: {},
                statuses: {},
                quantities: {},
                summa: 20,
                customerIdTmp: '',
                subIdTmp: '',
                typesColor: {
                    frozen: 'green',
                    transfer: 'red',
                    cloudpayments: 'yellow',
                },
            }
        },
        watch: {
            customerIdProp: function(newVal, oldVal) { // watch it
                console.log('Prop changed: ', newVal, ' | was: ', oldVal);
                this.customerId = newVal;
                if (this.customerId !== null) {
                    this.getCustomerWithData();
                    this.getLogs();
                }
            },
            customer: function (newVal, oldVal) {
                if (newVal.phone.length == 10) {
                    newVal.phone = '7' . newVal.phone;
                }
                this.customer = newVal;
            }
        },
        mounted() {
            if (this.type == 'create') {
                this.addProduct();
            } else if (this.type == 'edit') {
            }

            this.getOptions();
        },
        methods: {
            genlink(subId){

                this.subIdTmp = subId;
                axios.post('/pitech/get-customer-id', { subscription: subId })
                   .then(response => {
                        this.customerIdTmp = response.data[0].customer_id;
                        this.getLinkPitech();
                        //  console.log(this.subscriptionLogs);
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error(error);
                    });
            },
            getLinkPitech(){
                var settings = {
                    "url":  "https://cards-stage.pitech.kz/gw/cards/save",
                    //бой ниже
                   // "url": "https://cards.pitech.kz/gw/cards/save",
                    "method": "POST",
                    "timeout": 0,
                    "headers": {
                        "Authorization": "Basic NjBQWS1MWnluZGNQVl9LQzhjTm5tZW9oLTg2c2Y1MHA6VVA3WWxEa3pzZ3pYS2p2T2dMNjQxdEpOOFpnTUhEWXY=",
                        "Content-Type": "application/json"
                    },
                    "data": JSON.stringify({
                        "extClientRef": this.customerIdTmp,
                        "errorReturnUrl": "https://www.strela-academy.ru/card-save-fail",
                        "successReturnUrl": "https://www.strela-academy.ru/card-saved",
                        "callbackSuccessUrl": "http://www.strela-academy.ru/api/pitech/save-success",
                        "callbackErrorUrl": "http://www.strela-academy.ru/api/pitech/save-success",
                        "amount": this.summa,
                        "extOrdersId": this.subIdTmp,
                        "expirationTimeSeconds": 172800,
                        "currency": "KZT",
                        "createdBy": "user"
                    }),
                };

                // var settings = {
                //     "url": "https://cards-stage.pitech.kz/gw/cards/save",
                //     "method": "POST",
                //     "timeout": 0,
                //     "headers": {
                //         "Authorization": "Basic c2RJY2hNS3VTcVpza3BFOVdvVC1nSG9jSnhjd0xrbjY6WmxwYVJZTkFDbUJhR1Utc0RpRFEzUVM1RFhVWER0TzI=",
                //         "Content-Type": "application/json"
                //     },
                //     "data": JSON.stringify({
                //         "extClientRef": this.customerIdTmp,
                //         "successReturnUrl": "http://test.strela-academy.ru/card-saved",
                //         "errorReturnUrl": "http://test.strela-academy.ru/card-save-fail",
                //         "callbackSuccessUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
                //         "callbackErrorUrl": "http://test.strela-academy.ru/api/pitech/pay-success",
                //         "amount": this.summa,
                //         "expirationTimeSeconds": 172800,
                //         "extOrdersId": this.subIdTmp,
                //         "currency": "KZT",
                //         "createdBy": "user"
                //     }),
                // };

                $.ajax(settings).done(function (response) {

                    $(".cardLink").html("<a href='"+response.paymentUrl+"' target='_blank'>"+response.paymentUrl+"</a> <input type='hidden' id='cardLinkInput' value='"+response.paymentUrl+"'>");
                    $('#genBtn').hide();
                    $('#pitechlogo').show();
                  //  console.log(response);
                });
            },
            getLogs(){
                //console.log(this.subscriptionIdProp);
                axios.get('/userlogs/list', {
                    params: {
                        subscription_id: this.subscriptionIdProp
                    }
                })
                    .then(response => {
                        this.subscriptionLogs = response.data.data;
                        //  console.log(this.subscriptionLogs);
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error(error);
                    });
            },
            selectPrice(event) {
                this.currentPrice = event.target.value;
            },
            paymentByPitechCard(subId, cardId){
                this.spinnerData.loading = true;
                if(!subId || !cardId){
                    alert('Сохраните пожалуйста карточку');
                }
                axios.post('/pitech/payWithCard', { subId, cardId })
                    .then(response => {
                        this.spinnerData.loading = false;
                        console.log(response);
                        if(response.data.code){
                            Vue.$toast.error(response.data.code+" - "+JSON.stringify(response.data.parameters));
                        }
                        else if(!response.data){
                            Vue.$toast.error('Ошибка оплаты');
                        }
                        else {
                            Vue.$toast.success('Запрос на оплату успешно отпрвлен. Проверьте через одну минуту. ');
                        }
                    })
                    .catch(err => {
                        this.spinnerData.loading = false;
                        Vue.$toast.error(err);
                    });

            },
            changeAmount(cpId, subId, product){
                if (confirm("Хотите изменить стоимость подписки?")){
                    axios.post('/cloudpayments/updateamount', {
                        cpId: cpId,
                        Amount: this.currentPrice,
                        subscriptionId: subId,
                        product: product
                    })
                        .then(function (response) {
                           //  console.log(response.data);(response);
                            if(response.data){
                                $('#change-price').hide();
                                Vue.$toast.success('"Стоимость подписки успешно изменена!"');
                            }
                            else{
                                Vue.$toast.error('Подписка не может быть изменена!');
                            }

                        })
                        .catch(function (error) {
                            console.log(error);
                            Vue.$toast.error(error);
                        });
                    this.submit();

                } else {
                    console.log(this.customer.subscriptions[0].id);
                    return false;
                }
            },
            isDisabled(subscription) {
                if (this.userRole == 'head' || this.userRole == 'host' || this.userRole == 'operator') {
                    return false;
                }

                if (subscription.team_id) {
                    return ! this.userTeamIds.includes(subscription.team_id);
                } else {
                    return false;
                }
            },
            writeOffPaymentByToken(subId, cardId) {
                document.getElementById('writeOffPaymentByToken-' + subId).disabled = true;
                this.spinnerData.loading = true;
                axios.post('/subscriptions/writeOffPaymentByToken', {
                    subscriptionId: subId,
                    cardId: cardId
                }).then(response => {
                    this.spinnerData.loading = false;
                    document.getElementById('writeOffPaymentByToken-' + subId).disabled = false;
                    Vue.$toast.success(response.data.message);
                })
                    .catch(err => {
                        this.spinnerData.loading = false;
                        document.getElementById('writeOffPaymentByToken-' + subId).disabled = false;
                        Vue.$toast.error(err.response.data.message);
                    });
            },
            showTab(subIndex) {
                let k = 0;
                this.subscriptions.forEach((sub, key) => {
                    if (sub.id == this.subscriptionIdProp) {
                        k = key;
                    }
                });

                if (subIndex == k) {
                    return true;
                } else {
                    return false;
                }
            },
            manualWriteOffPayment(subscriptionId, cardId) {
                document.getElementById('subscription-' + subscriptionId).disabled = true;
                this.spinnerData.loading = true;

                axios.post('/subscriptions/manualWriteOffPayment', {
                    subscriptionId: subscriptionId,
                    cardId: cardId
                }).then(response => {
                    this.spinnerData.loading = false;
                    document.getElementById('subscription-' + subscriptionId).disabled = false;
                    Vue.$toast.success(response.data.message);
                })
                    .catch(err => {
                        this.spinnerData.loading = false;
                        document.getElementById('subscription-' + subscriptionId).disabled = false;
                        Vue.$toast.error(err.response.data.message);
                    });
            },
            manualPitech(customer, subId, product, price){
                if (confirm('Вы действительно хотите списать средства')) {
                    this.spinnerData.loading = true;

                    axios.post('/pitech/manualPayment', {
                        customer: customer,
                        subId: subId,
                        product: product,
                        price: price
                    }).then(response => {
                        this.spinnerData.loading = false;
                        if(response.data.paymentResponseCode == "OK"){
                            Vue.$toast.success('Оплата прошла успешно.  Обновите страницу!');
                        }
                        else if(response.data.code){
                            Vue.$toast.error('Ошибка '+response.data.code);
                        }
                        else {
                            Vue.$toast.error('Карта не найдена! ');
                        }

                    })
                        .catch(err => {
                            this.spinnerData.loading = false;
                            Vue.$toast.error(err);
                        });
                }
            },
            getSubscriptionTitle(productId) {
                if (productId) {
                  //  console.log(productId);
                    return this.products[productId].title;
                } else {
                    return 'Новый абонемент';
                }
            },
            getProductBlockAmount(productId) {
                if (productId) {
                    this.summa = this.products[productId].block_amount;
                    return this.products[productId].block_amount;
                } else {
                    return 'Новый абонемент';
                }
            },
            openTransferModal() {
                $('#modalTransfer').modal('toggle');
            },
            hideCloudpaymentsModal(index) {
                $('#modalCloudpayments-' + index).modal('hide');
            },
            showCloudpaymentsModal(index) {
                $('#modalCloudpayments-' + index).modal('show');
            },
            hideTransferModal(id, toDate, index) {
                this.subscriptions[index].ended_at = toDate;
                // $('#modalTransfer-' + id).modal('hide');
                this.$bvModal.hide('modalTransfer-' + id);
            },
            showTransferModal(id) {
                // $('#modalTransfer-' + id).modal('show');
                this.$bvModal.show('modalTransfer-' + id);
            },
            hideHistorySubscriptionModal(id) {
                $('#modalHistorySubscription-' + id).modal('hide');
            },
            showHistorySubscriptionModal(id) {
                $('#modalHistorySubscription-' + id).modal('show');
            },
            deletePayment(payment, paymentIndex, subIndex) {
                if (payment.id) {
                    let result = confirm('Удалить платеж?');
                    if (result) {
                        axios.delete(`/payments/${payment.id}`).then(response => {
                            let message = response.data.message;
                            if (response.data.message) {
                                Vue.$toast.success(message);
                                this.subscriptions[subIndex].payments.splice(paymentIndex, 1);
                            }
                        })
                            .catch(err => {
                                if (err.response.status === 422) {
                                    let errors = err.response.data.errors;
                                    if (errors) {
                                        Object.keys(errors).forEach(function(name) {
                                            Vue.$toast.error(errors[name][0]);
                                        });
                                    }
                                }
                                throw err;
                            });
                    }
                }
            },
            setDates(startedAt, subIndex) {
                if (this.type == 'create') {
                    this.subscriptions[subIndex].ended_at = moment().format();
                    this.subscriptions[subIndex].tries_at = moment().add(7, 'days').format();
                }
            },
            showDate(date) {
                return moment(date).locale('ru').format('DD MMM YY');
            },
            showDateWithTrial(date, days) {
                return moment(date).locale('ru').add(days, 'days').format('DD MMM YY');
            },
            closeModal() {
                this.customer = {
                    name: '',
                    phone: '',
                    email: '',
                    comments: '',
                };
                this.customerId = null;
                this.customerIdProp = null;
                this.subscriptions = [];
                this.addProduct();
                this.$bvModal.hide('modal-customer-' + this.type);
                this.$bvModal.hide('modal-customer-create');
            },
            copyRecurrentLink(index) {
                var input = $('#recurrent-link-' + index);
                var success   = true,
                    range     = document.createRange(),
                    selection;

                // For IE.
                if (window.clipboardData) {
                    window.clipboardData.setData("Text", input.val());
                } else {
                    // Create a temporary element off screen.
                    var tmpElem = $('<div>');
                    tmpElem.css({
                        position: "absolute",
                        left:     "-1000px",
                        top:      "-1000px",
                    });
                    // Add the input value to the temp element.
                    tmpElem.text(input.val());
                    $("body").append(tmpElem);
                    // Select temp element.
                    range.selectNodeContents(tmpElem.get(0));
                    selection = window.getSelection ();
                    selection.removeAllRanges ();
                    selection.addRange (range);
                    // Lets copy.
                    try {
                        success = document.execCommand("copy", false, null);
                    }
                    catch (e) {
                        copyToClipboardFF(input.val());
                    }
                    if (success) {
                        Vue.$toast.success('Ссылка скопирована!');

                        // remove temp element.
                        tmpElem.remove();
                    }
                }
            },
            copyGenLink(index) {
                var input = $('#cardLinkInput');
                var success   = true,
                    range     = document.createRange(),
                    selection;

                // For IE.
                if (window.clipboardData) {
                    window.clipboardData.setData("Text", input.val());
                } else {
                    // Create a temporary element off screen.
                    var tmpElem = $('<div>');
                    tmpElem.css({
                        position: "absolute",
                        left:     "-1000px",
                        top:      "-1000px",
                    });
                    // Add the input value to the temp element.
                    tmpElem.text(input.val());
                    $("body").append(tmpElem);
                    // Select temp element.
                    range.selectNodeContents(tmpElem.get(0));
                    selection = window.getSelection ();
                    selection.removeAllRanges ();
                    selection.addRange (range);
                    // Lets copy.
                    try {
                        success = document.execCommand("copy", false, null);
                    }
                    catch (e) {
                        copyToClipboardFF(input.val());
                    }
                    if (success) {
                        Vue.$toast.success('Ссылка скопирована!');

                        // remove temp element.
                        tmpElem.remove();
                    }
                }
            },
            copyPitechLink(index) {
                var input = $('#pitech-link-' + index);
                var success   = true,
                    range     = document.createRange(),
                    selection;

                // For IE.
                if (window.clipboardData) {
                    window.clipboardData.setData("Text", input.val());
                } else {
                    // Create a temporary element off screen.
                    var tmpElem = $('<div>');
                    tmpElem.css({
                        position: "absolute",
                        left:     "-1000px",
                        top:      "-1000px",
                    });
                    // Add the input value to the temp element.
                    tmpElem.text(input.val());
                    $("body").append(tmpElem);
                    // Select temp element.
                    range.selectNodeContents(tmpElem.get(0));
                    selection = window.getSelection ();
                    selection.removeAllRanges ();
                    selection.addRange (range);
                    // Lets copy.
                    try {
                        success = document.execCommand("copy", false, null);
                    }
                    catch (e) {
                        copyToClipboardFF(input.val());
                    }
                    if (success) {
                        Vue.$toast.success('Ссылка скопирована!');

                        // remove temp element.
                        tmpElem.remove();
                    }
                }
            },
            getCustomerWithData() {
                this.spinnerData.loading = true;
                axios.get('/customers/' + this.customerId + '/with-data').then(response => {
                    let data = response.data.data;
                    if (response.data.data) {
                        this.spinnerData.loading = false;
                        if (data.phone.length == 10) {
                            data.phone = '+7' + data.phone;
                        }
                        this.customer = data;
                        this.subscriptions = data.subscriptions;
                        this.userTeamIds = data.userTeamIds;
                        this.userRole = data.userRole;
                        let typesColor = this.typesColor;
                        this.subscriptions.forEach((subscription, subIndex, selfSubscriptions) => {
                            let hstr = [];
                            Object.keys(subscription.history).forEach(function(historyIndex) {
                                let attr = {};
                                attr.highlight = typesColor[historyIndex];
                                attr.dates = [];
                                subscription.history[historyIndex].forEach((payment, paymentIndex, selfPayments) => {
                                    attr.dates.push(payment.dates);
                                });
                                hstr.push(attr);
                            });
                            this.subscriptions[subIndex].history = hstr;
                        });
                    }
                })
                    .catch(err => {
                        throw err;
                    });
            },
            getOptions() {
                axios.get('/customers/get-options').then(response => {
                    this.paymentTypes = response.data.paymentTypes;
                    this.statuses = response.data.statuses;
                    this.quantities = response.data.quantities;
                    this.users = response.data.users;
                    this.user = response.data.user;
                    this.userTeamIds = response.data.userTeamIds;
                    this.userRole = response.data.userRole;
                    this.teamsProp = response.data.teams;
                });
            },
            setFileToSubscription(value, index) {
                this.subscriptions[index].newPayment.check = value;
            },
            removeClassInvalid() {
                var paras = document.getElementsByClassName('is-invalid');

                while(paras.length > 0){
                    paras[0].classList.remove('is-invalid');
                }
            },
            submit() {
                this.spinnerData.loading = true;
                let data = {
                    customer: {
                        name: this.customer.name,
                        phone: this.customer.phone,
                        email: this.customer.email,
                        comments: this.customer.comments,
                    },
                    subscriptions: this.subscriptions,
                };

                //console.log(this.subscriptions);
                if (this.type != 'create') {
                    data.customer.id = this.customer.id;
                }
                this.removeClassInvalid();
                axios.post('/customers/update-with-data', data).then(response => {
                    this.spinnerData.loading = false;
                    let customer = response.data.customer;
                    if (response.data.customer) {
                        this.customer = {
                            id: customer.id,
                            name: customer.name,
                            phone: customer.phone,
                            email: customer.email,
                            comments: customer.comments,
                            card: customer.card,
                        };

                        this.subscriptions = customer.subscriptions;
                    }

                    // this.subscriptions.forEach((value, index, self) => {
                    //     if (value.payment_type != 'cloudpayments' || value.payment_type != 'simple_payment') {
                    //         this.customer = {
                    //             name: '',
                    //             phone: '',
                    //             email: '',
                    //             comments: '',
                    //         };

                    //         this.subscriptions = [];
                    //         this.addProduct();
                    //         this.customerId = null;
                    //         this.customerIdProp = null;
                    //         this.subscriptionId = null;
                    //         this.subscriptionIdProp = null;
                    //         $('#modal-customer-create').modal('hide');
                    //         $('#modal-customer-edit').modal('hide');
                    //     }
                    // });
                    if (document.getElementById("file")) {
                        document.getElementById("file").value = "";
                    }

                    Vue.$toast.success(response.data.message);
                })
                    .catch(err => {
                        this.spinnerData.loading = false;
                        if (err.response.status === 422) {
                            let errors = err.response.data.errors;
                            if (errors) {
                                Object.keys(errors).forEach(function(name) {
                                    let element = document.getElementsByName(name)[0];
                                    if (element) {
                                        element.classList.add('is-invalid');
                                    }
                                    Vue.$toast.error(errors[name][0]);
                                });
                            }
                        }
                        throw err;
                    });
            },
            removeProduct(id, subIndex) {
                if (this.type == 'create' || !id) {
                    if (subIndex > -1) {
                        if (this.subscriptions.length > 1) {
                            this.subscriptions.splice(subIndex, 1);
                        }
                    }
                } else if (this.type == 'edit') {
                    let result = confirm('Удалить подписку?');
                    if (result) {
                        axios.delete(`/subscriptions/${id}`).then(response => {
                            let message = response.data.message;
                            if (response.data.message) {
                                this.subscriptions.splice(subIndex, 1);
                                Vue.$toast.success(message);
                            }
                        })
                            .catch(err => {
                                if (err.response.status === 422) {
                                    let errors = err.response.data.errors;
                                    if (errors) {
                                        Object.keys(errors).forEach(function(name) {
                                            Vue.$toast.error(errors[name][0]);
                                        });
                                    }
                                }
                                throw err;
                            });
                    }
                }
            },
            addProduct() {
                let now = moment();

                this.subscriptions.push({
                    id: null,
                    product_id: null,
                    reason_id: null,
                    team_id: null,
                    user_id: null,
                    price: null,
                    payment_type: null,
                    started_at: now.format(),
                    paused_at: null,
                    ended_at: moment().format(),
                    frozen_at: null,
                    defrozen_at: null,
                    tries_at: moment().add(7, 'days').format(),
                    status: null,
                    description: null,
                    newPayment: {
                        from: null,
                        to: null,
                        quantity: null,
                        check: null,
                    },
                    product: {
                        id: null,
                        title: null,
                        price: null,
                    },
                });
            },
            getProductsWithPrices() {
                axios.get(`/products/with-prices`).then(response => {
                    this.products = response.data;
                });
            },
            getPrices(productId) {
                if (productId) {
                    return this.products[productId]['prices'];
                }
                return [];
            },
            getPaymentTypes(productId) {
                if (productId) {
                    return this.paymentTypes[productId];
                }
                return [];
            },
            getStatuses(productId, paymentTypeKey) {
                if (paymentTypeKey && this.paymentTypes[productId] && this.paymentTypes[productId][paymentTypeKey] && this.paymentTypes[productId][paymentTypeKey]!==undefined) {
                    return this.paymentTypes[productId][paymentTypeKey]['statuses'];
                }
                return [];
            }
        },
        created() {
            this.getProductsWithPrices();
        },
    }
</script>
<style scoped>
    .list-payment-Completed {
        background: #00da1b40;
    }
    .list-payment-Declined {
        background: #cc1f1f38;
    }
    .b-tabs .card-header {
        background-color: rgba(0,0,0,.03)!important;
        border-bottom: 1px solid rgba(0,0,0,.125)!important;
    }
    .ended_at-span {
        padding: 5px 0px;
        padding-right: 10px;
        display: inline-block;
    }
    .v-spinner {
        width: 100%;
        height: 100%;
        text-align: center;
        position: absolute;
        background: #00000017;
    }
    .recurrent_block {
        padding: 20px;
        border: 1px solid #3490dc;
    }
    .recurrent_button-block {
        margin: 15px;
    }
    .selectpicker {
        display: block!important;
    }
    .bd-example {
        padding: 1.5rem;
        padding-top: 0px;
        padding-bottom: 0px;
        margin-right: 0;
        margin-left: 0;
    }
    .list-group-remove {
        color: #ffffff;
        background-color: #3490dc;
        float: right;
        display: inline-block;
        border-radius: 10rem;
        padding: 2px 6px;
        font-size: 9px;
        font-weight: 700;
        cursor: pointer;
    }
</style>
