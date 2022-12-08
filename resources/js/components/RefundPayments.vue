<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Возарат средств</h2>

            <div class="intro">
              CloudPayments
            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <b>Период</b>

                    <div class="row" style="padding-top: 20px; ">
<!--                        <div class="col-1">-->
<!--                            &nbsp<br>-->
<!--                            <button id="getDelay" v-if="!this.delay"  @click="getDelay()"  type="button" class="btn btn-outline-danger  btn-sm">Просрочка</button>-->
<!--                            <button id="get" v-else  @click="getTries()"  type="button" class="btn btn-outline-success  btn-sm">Пробуют</button>-->
<!--                        </div>-->
                        <div class="col-2">
                            Дата
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
                            &nbsp<br>
                            <button id="getlist" v-if="startDate"  @click="waitingPayList()"  type="button" class="btn btn-success btn-sm">Получить данные</button>
                        </div>
                    </div>


                </div>
                <div class="card-body">
                    <strong>Всего записей -      {{items.length}}</strong><br>
                    <h5 style="margin-top: 15px; color: #0069d9;">{{info}}</h5><br>

                    <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Дата</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Транзакция</th>
                            <th scope="col">Услуга</th>
                            <th scope="col">Банк </th>
                            <th scope="col">Процесс</th>
                            <td></td>
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="(item, index) in items" :key="index">
                            <td>{{ index+1 }}  </td> <!--- {{item.customer_id}} -->
                            <td>{{item.AccountId}}</td>
<!--                            <td>{{ item.Amount}}</td>-->
                            <td>{{ item.PayoutAmount }}</td>
<!--                            <td>{{ item.Type}}</td>-->
                            <td>{{ item.CreatedDate }}</td>
                            <td>{{ item.Reason}}</td>
<!--                            <td>{{item.Status}}</td>-->
<!--                            <td>{{ item.StatusCode}}</td>-->
                            <td>{{ item.TransactionId }}</td>
<!--                            <td>{{ item.Refunded }}</td>-->
                            <td>{{ item.Description}}</td>
                            <td>{{item.GatewayName}}</td>
                            <td>{{ item.CardHolderMessage}}</td>
                            <td>
                                <a target="_blank" :href="'/userlogs?subscription_id=' + item.AccountId">Логи</a>
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
            info: '',
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
                this.spinnerData.loading = true;
                axios.post('/reports/get-refunds',{
                    type: 'cloudpayments',
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD'),
                })
                    .then(response => {
                        //console.log(response.data.Model);
                        response.data.Model.forEach(elem =>{

                                  console.log(elem);

                                  if(elem.Type == 1) {
                                      this.info = '';
                                      this.items.push({
                                          AccountId: elem.AccountId,
                                          Amount: elem.Amount,
                                          CreatedDate: moment(elem.CreatedDate).locale('ru').format('DD MMM YY'),
                                          PayoutAmount: elem.PayoutAmount,
                                          Reason: elem.Reason,
                                          Type: elem.Type,
                                          Status: elem.Status,
                                          StatusCode: elem.StatusCode,
                                          TransactionId: elem.TransactionId,
                                          Refunded: elem.Refunded,
                                          Description: elem.Description,
                                          GatewayName: elem.GatewayName,
                                          CardHolderMessage: elem.CardHolderMessage,
                                      });
                                      this.spinnerData.loading = false;
                                  }

                                  if(this.items.length < 1){
                                      this.info = 'Нет данных на выбраную дату!';
                                      this.spinnerData.loading = false;
                                  }

                        });

                    })
                    .catch(function (error) {
                        console.log('err');
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
