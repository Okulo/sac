<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Жду оплату</h2>

           <div class="intro"> Сюда попадают те обонементы, за которые клиенты пообещали оплатить, но нужно проконтролировать,
            что оплата прошла удачно. <br>Если оплата по абонементу выполнена,
            то статус поменяется на оплачено автоматически
            и клиента уже не будет в этом списке после обновления страницы.
            <p></p>
            Список обновляется при обновлении страницы
           </div>
            <div class="card mt-3">
                <div class="card-header">
                    <div style="line-height: 25px; cursor: pointer;"  @click="filterOpen = !filterOpen">
                        <small class="float-right">
                            <a id="filter-toggle" class="btn btn-default btn-sm" title="Скрыть/показать">
                                <i class="fa fa-toggle-off " :class="{'fa-toggle-on': filterOpen}"></i>
                            </a>
                        </small>
                    </div>

                    <div class="row" v-show="filterOpen" :class="{slide: filterOpen}">
                        <div class="col-3">
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
                        <div class="col-3">
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
                        <div class="col-3">
                            &nbsp<br>
                            <button id="getlist" v-if="startDate && endDate"  @click="getSubscriptionlist()"  type="button" class="btn btn-success btn-sm">Получить данные</button>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <strong>Всего записей -      {{items.length}}</strong><p><br></p>
                    <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Клиенты</th>
                            <th scope="col">Телефон</th>
                            <th scope="col">Услуги</th>
                            <th scope="col">Дата<br> старта</th>
                            <th scope="col">Дата <br> окончания</th>
                            <th scope="col">В <br>процессе</th>
                            <td></td>
                          <!--  <th scope="col"></th> -->
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="(item, index) in items" :key="index">

                            <td>{{ index+1 }}  </td> <!--- {{item.customer_id}} -->
                            <td>
                                <a class="custom-link" role="button" @click="openModal(item.customer_id, item.id)">{{item.name}}</a>
                            </td>
                            <td>{{item.phone}}</td>
                            <td>{{ item.ptitle}}</td>
                            <td>{{ item.started_at }}</td>
                            <td>{{ item.ended_at}}</td>
                        <!--    <td>{{item.payment_type}}</td>
                            <td>{{item.status}}</td>-->
                            <td>
                            <!--    <input  v-model="processed"  v-if="item.process_status == 1" class="form-check-input" type="checkbox" value="1" checked="true" id="checked">
                                <input  v-model="processed"  v-if="item.process_status == null" class="form-check-input" type="checkbox" value="item.phone" id="item.phone">
                                 <button v-if="item.process_status == null" type="button" class="btn btn-outline-info btn-sm">В процессе</button>
                                 -->
                                <input v-if="item.process_status == 1" class="form-check-input" type="checkbox" value="1" checked="true" id="checked">
                                <input v-if="item.process_status == null" type="checkbox" :value="item.id" id="item.id" class="form-check-input" v-model="processed" @change="goProcess($event)">
                            </td>
                            <td>    <a target="_blank" :href="'/userlogs?subscription_id=' + item.id">Логи</a></td>
                            <!-- <td><button data-v-9097e738=""  @click="cheked(item.id)" class="btn btn-outline-info">Обработано</button></td> -->
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
            processed: [],
            items: [],
            cpData: '',
            cp: '',
            cpStatus: [],
            spinnerData: {
                loading: false,
                color: '#6cb2eb',
                size: '60px',
            },
            statusStyle : '',
            period: 'week'
        }),
        mounted() {
            console.log('Component mounted.');
            this.waitingPayList();
          //  this.getSubscriptionlist();
        },
        computed: {
            //функция сортировки массива

        },
        methods: {
            goProcess: function(e) {
                if (this.processed.length > 0){

                    axios.post('/reports/set-processed-status',{
                        subId: this.processed.toString(),
                        status: 1
                    })
                        .then(response => {
                            this.waitingPayList();
                        })
                        .catch(function (error) {
                            console.log('err');
                            console.log(error);
                            Vue.$toast.error('error - '+ error);
                        });

                    console.log(this.processed);
                }
            },
            waitingPayList(){
                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/get-waiting-pay-list', {
                    period: this.period,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59')
                })
                    .then(response => {
                        this.spinnerData.loading = false;
                        response.data.forEach(elem =>{
                            //   console.log(elem.data);

                            this.items.push({
                                id: elem.id,
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
                                process_status: elem.process_status
                            });
                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
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
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59')
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
