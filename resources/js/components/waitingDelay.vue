<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Пробники</h2>

            <div class="intro">
                Сюда попадают те обонементы, за которые клиенты пообещали оплатить, но нужно проконтролировать, что оплата прошла удачно. Также здесь могут быть пробники у которых закончился тестовый период но их еще не отключили.
                <br>
                Если оплата по абонементу выполнена, то статус поменяется на оплачено автоматически и клиента уже не будет в этом списке после обновления страницы.хрх
                <p></p>
                Список обновляется при обновлении страницы
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <b>Фильтр</b>

                    <div class="row" style="padding-top: 20px; ">
<!--                        <div class="col-1">-->
<!--                            &nbsp<br>-->
<!--                            <button id="getDelay" v-if="!this.delay"  @click="getDelay()"  type="button" class="btn btn-outline-danger  btn-sm">Просрочка</button>-->
<!--                            <button id="get" v-else  @click="getTries()"  type="button" class="btn btn-outline-success  btn-sm">Пробуют</button>-->
<!--                        </div>-->
                        <div class="col-2">
                            С даты окончания
                            <datetime
                                type="date"
                                v-model="startDate"
                                input-class="form-control"
                                valueZone="Asia/Almaty"
                                value-zone="Asia/Almaty"
                                zone="Asia/Almaty"
                                :auto="true"
                            ></datetime>
                        </div>
                        <div class="col-2">
                            По дату окончания
                            <datetime
                                type="date"
                                v-model="endDate"
                                input-class="form-control"
                                valueZone="Asia/Almaty"
                                value-zone="Asia/Almaty"
                                zone="Asia/Almaty"
                                :auto="true"
                            ></datetime>
                        </div>
                        <div class="col-2">
                            Оператор
                            <select v-model="user" class="custom-select">
                                <option v-for="user in users" v-bind:value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-3">
                            Услуги
                            <select v-model="product" class="select-multiple custom-select">
                                <option v-for="product in products" v-bind:value="product.id">
                                    {{ product.title }}
                                </option>
                            </select>
                        </div>
                        <div class="col-2">
                            &nbsp<br>
                            <button id="getlist" v-if="startDate && endDate || product || user"  @click="waitingPayList()"  type="button" class="btn btn-success btn-sm">Получить данные</button>
                        </div>
                    </div>


                </div>
                <div class="card-body">
                    <strong>Всего записей -      {{items.length}}</strong><br>
                    <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Клиенты</th>
                            <th scope="col">Услуги</th>
                            <th>Осталось <br> дней</th>
                            <th scope="col">Дата<br> старта</th>
                            <th>Пробный до</th>
                            <th scope="col">Статус </th>
                            <th scope="col">В <br>процессе</th>
                            <th scope="col">Примечания</th>
                            <td></td>
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="(item, index) in items" :key="index">

                            <td>{{ index+1 }}  </td> <!--- {{item.customer_id}} -->
                            <td>
                                <a class="custom-link" role="button" @click="openModal(item.customer_id, item.id)">{{item.name}}</a>
                            <br>{{item.phone}}
                            </td>

                            <td style="width: 220px">{{ item.ptitle}}</td>
                            <td>{{ item.calcDate }}</td>
                            <td>{{ item.started_at }}</td>
                            <td>{{ item.tries_at}}</td>
                            <!--   <td>{{item.paymentsCount}}</td>

                             <td>{{ item.ended_at}}</td> -->
                            <!--    <td>{{item.payment_type}}</td>
                                <td>{{item.status}}</td>-->
                            <td>
                                <select id="status" style="width: 100px" name="status" :class="'status-'+item.id">
                                    <option selected disabled="disabled" value="tries">Пробует</option>
                                    <option value="waiting">Жду оплату</option>
                                    <option value="paid">Оплачено</option>
                                    <option value="rejected">Отклонена (3 раза)</option>
                                    <option value="refused">Отказался</option>
                                    <option value="trial">Триал период</option>
                                    <option value="debtor">Должник</option>
                                </select>
                            </td>
                            <td style="text-align: center">
                                {{item.report_type}}
                                <input v-model="item.process_status" v-if="item.process_status == 0 || !item.process_status"  type="checkbox" :value="item.id" id="item.id" class="form-check-input" @change="goProcess(item.id)">
                                <input v-model="item.process_status" v-if="item.process_status == 1" class="form-check-input" type="checkbox" value="1" checked="true" id="checked" @change="unprocess(item.id)">
                            </td>
                            <td style="width: 250px">{{ item.comments}}</td>
                            <td>    <a target="_blank" :href="'/userlogs?subscription_id=' + item.id">Логи</a></td>
                            <td data-v-754b2df6="" class="text-right">
                                <button type="button" title="Сохранить" class="btn btn-danger btn-sm save-button"  @click="saveStatus(item.id)">
                                    <i class="fa fa-save"></i></button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
        <customer-component type-prop="edit" :subscription-id-prop="subscriptionId" :customer-id-prop="customerId"></customer-component>

    </div>
</template>

<script>
    import moment from 'moment';
    import CustomerComponent from './CustomerComponent.vue';

    export default {
        components: { CustomerComponent },
        props: [
            'prefixProp',
            'createLinkProp',
        ],
        data: () => ({
            startDate: '',
            endDate: '',
            customerId: null,
            subscriptionId: null,
            filterOpen: false,
            processed: '',
            items: [],
            processedStatus: [],
            cpData: '',
            cp: '',
            cpStatus: [],
            spinnerData: {
                loading: false,
                color: '#6cb2eb',
                size: '60px',
            },
            statusStyle : '',
            period: '',
            products: {},
            product: '',
            proc: '',
            users:[],
            user: '',
        }),
        mounted() {
            console.log('Component mounted.');
            this.waitingPayList();
            //  this.getSubscriptionlist();
            this.getUserList();
        },

        created() {
            this.getProductsWithPrices();
        },
        computed: {
            //функция сортировки массива

        },
        methods: {
            getDelay() {
                this.delay = 1;
                this.daysHeader = 'Просрочено дн.';
                this.waitingPayList();
            },
            getTries() {
                this.delay = '';
                this.daysHeader = 'Осталось дн.';
                this.waitingPayList();
            },
            saveStatus(id){

                if( $(".status-"+id).val()){
                    var val = $(".status-"+id).val();

                    axios.post('/reports/save-status',{
                        subId: id,
                        status: val
                    })
                        .then(response => {
                            // this.waitingPayList();
                            console.log(response);
                            Vue.$toast.success('Статус успешно изменен');

                        })
                        .catch(function (error) {
                            console.log('err');
                            console.log(error);
                            Vue.$toast.error('error - '+ error);
                        });
                }

            },
            goProcess: function(id) {
                axios.post('/reports/set-processed-status',{
                    subId: id,
                    report_type: 13,
                    status: 1
                })
                    .then(response => {
                        // this.waitingPayList();
                        Vue.$toast.success('Статус успешно изменен');
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });

            },
            unprocess: function(id) {
                axios.post('/reports/set-processed-status',{
                    subId: id,
                    report_type: 13,
                    status: 0
                })
                    .then(response => {
                        // this.waitingPayList();
                        Vue.$toast.success('Статус успешно изменен');
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });

            },
            getUserList(){
                axios.get('/users/list', {
                    reportType: 5
                })
                    .then(response => {

                        //   console.log(response);
                        response.data.data.forEach(elem =>{
                            if(elem.is_active.value == 'Активный'){
                                //  console.log(elem);
                                this.users.push({
                                    id: elem.id.value,
                                    account: elem.account.value,
                                    name: elem.name.value,
                                });
                            }

                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            },
            waitingPayList(){
                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/get-delay-list', {
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59'),
                    product: this.product,
                    reportType: 13,
                    userId: this.user
                })
                    .then(response => {
                        this.spinnerData.loading = false;
                        response.data.forEach(elem =>{

                            //   console.log(elem);
                            var given = moment(elem.tries_at, "YYYY-MM-DD");
                            var current = moment().startOf('day');
                            var diff = moment.duration(current.diff(given)).asDays();

                                this.items.push({
                                    id: elem.id,
                                    calcDate:  Math.abs(diff),
                                    started_at: moment(elem.started_at).locale('ru').format('DD MMM YY'),
                                    tries_at: moment(elem.tries_at).locale('ru').format('DD MMM YY'),
                                    ended_at: moment(elem.ended_at).locale('ru').format('DD MMM YY'),
                                    updated_at: moment(elem.updated_at).locale('ru').format('DD MMM YY HH:mm'),
                                    status: elem.status,
                                    payment_type: elem.payment_type,
                                    name: elem.name,
                                    phone: elem.phone,
                                    customer_id: elem.customer_id,
                                    reason: elem.title,
                                    ptitle: elem.ptitle,
                                    process_status: elem.process_status,
                                    report_type: elem.report_type,
                                    paymentsCount: '',
                                    comments: elem.comments,
                                });
                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            },
            getUserPayments(subId){
                axios.post('/reports/get-user-payments', {
                    subId: subId//elem.customer_id
                })
                    .then(response => {
                        // console.log(response.data);
                        return response.data;
                        //     this.items.map(item => {
                        //     item.paymentsCount = response.data;
                        // });
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error(' ' + error);
                    });

            },
            getProductsWithPrices() {
                axios.get(`/products/with-prices`).then(response => {
                    this.products = response.data;
                });
            },
            openModal(customerId, subscriptionId) {
                this.customerId = null;
                this.subscriptionId = null;
                this.customerId = customerId;
                this.subscriptionId = subscriptionId;
                this.$bvModal.show('modal-customer-edit');
            },
            getSubscriptionlist(){
                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/get-refused-subscriptions-list', {
                    period: this.period,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59'),
                    product: this.product
                })
                    .then(response => {
                        this.spinnerData.loading = false;
                        response.data.forEach(elem =>{
                            //   console.log(elem.data);

                            this.items.push({
                                started_at: elem.started_at,
                                id: elem.id,
                                ended_at: moment(elem.ended_at).locale('ru').format('DD MMM YY'),
                                updated_at: moment(elem.updated_at).locale('ru').format('DD MMM YY HH:mm'),
                                status: elem.status,
                                payment_type: elem.payment_type,
                                name: elem.name,
                                phone: elem.phone,
                                customer_id: elem.customer_id,
                                reason: elem.title
                            });
                        });


                        // response.data.forEach(elem => {
                        // array =  JSON.parse(elem.request);
                        //
                        //     this.items.push({
                        //         account_id: array.AccountId,
                        //         status: array.Status,
                        //         amount: array.Amount,
                        //         subs_id: array.SubscriptionId,
                        //         date: array.DateTime,
                        //         transaction: array.TransactionId,
                        //
                        //     });
                        //
                        // })

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });


            },
            cheked(id){
                if (confirm("ID - "+id+"  обработан?")){
                    //console.log('id - '+id);
                    axios.post('/reports/add-wa-status', {
                        id: id,
                        waStatus: 1
                    })
                        .then(response => {
                            // console.log(response);
                            this.getSubscriptionlist();
                        })
                        .catch(function (error) {
                            console.log(error);
                            Vue.$toast.error(' ' + error);
                        });
                }
            },
            getlistWithDate(){
                console.log(moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01')+' - ' +moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59'))
            },
            sendInfo(item) {
                this.selectedUser = item;
            },

            getlist(){

                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/get-refused-list', {
                    period: this.period,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59')
                })
                    .then(response => {
                        this.spinnerData.loading = false;
                        response.data.forEach(elem =>{
                            // console.log(elem.data);

                            this.items.push({
                                started_at: elem.started_at,
                                id: elem.id,
                                ended_at: moment(elem.ended_at).locale('ru').format('DD MMM YY'),
                                updated_at: moment(elem.updated_at).locale('ru').format('DD MMM YY'),
                                status: elem.status,
                                payment_type: elem.payment_type,
                                name: elem.name,
                                phone: elem.phone,
                                customer_id: elem.customer_id,
                                reason: elem.title
                            });
                        });


                        // response.data.forEach(elem => {
                        // array =  JSON.parse(elem.request);
                        //
                        //     this.items.push({
                        //         account_id: array.AccountId,
                        //         status: array.Status,
                        //         amount: array.Amount,
                        //         subs_id: array.SubscriptionId,
                        //         date: array.DateTime,
                        //         transaction: array.TransactionId,
                        //
                        //     });
                        //
                        // })

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });


            },
            getCpStatus(){
                let cpStatus = [];
                //this.items.forEach(elem => console.log(this.getCpData(elem.subscription_id)));

                this.items.forEach(elem => {
                    if(elem.subscription_id) {

                        axios.post('/reports/getSubscription', {
                            id: elem.subscription_id
                        })
                            .then(response => {
                                // .push({ cp_status: response.data.Model.Status })
                                this.items.map(item => {
                                    item.subscription_id !== response.data.Model.Id ? item : item.cp_status = response.data.Model.Status;
                                    this.statusStyle = response.data.Model.Status;
                                });
                            })
                            .catch(function (error) {
                                console.log(error);
                                Vue.$toast.error(' ' + error);
                            });
                    }
                });

            },
            getCpData(id){

                axios.post('/reports/getSubscription', {
                    id: id
                })
                    .then(response => {
                        console.log('getCpData');
                        // console.log(response.data.Success);
                        //console.log(response.data.Model);
                        this.cpData = response.data.Model;
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error(' '+error);
                    });
            },
        }
    }
</script>
<style scoped>
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    tr{
        font-size: 15px;
    }
    th{
        font-size: 0.8rem;
    }
    .intro{
        font-size: 15px;
        padding: 10px 0;
        border-top: 1px solid #dee2e6;
    }

    .Active {
        color: #00d25c;
    }
    .Rejected{
        color: #d32535;
    }
</style>
