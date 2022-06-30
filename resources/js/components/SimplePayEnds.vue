<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Заканчиваются по прямым переводам</h2>

           <div class="intro">
               Сюда попадают абонементы у которых осталось 5 и менее дней до окончания абонемента по прямому переводу
            <p></p>
            Список обновляется при обновлении страницы
           </div>
            <div class="card mt-3">
                <div class="card-header">
                    Фильтр
                    <small class="float-right">
                        <a id="filter-toggle"  @click="filterOpen = !filterOpen" class="btn btn-default btn-sm" title="Скрыть/показать">
                            <i class="fa fa-toggle-off " :class="{'fa-toggle-on': filterOpen}"></i>
                        </a>
                    </small>
                    <div class="row" style="padding-top: 20px; " v-show="filterOpen" :class="{slide: filterOpen}">
                        <br>
                        <b>Услуги</b>
                        <p></p>
                        <div class="col-4">
                           <select v-model="product" class="select-multiple custom-select">
                                <option v-for="product in products" v-bind:value="product.id">
                                    {{ product.title }}
                                </option>
                            </select>
                        </div>

                        <div class="col-4">
                            <button id="getlist" v-if="startDate && endDate || product"  @click="getList()"  type="button" class="btn btn-success btn-sm">Получить данные</button>
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
                            <th >Ост. дней</th>
                            <!--  <th>Кол-во <br> платежей</th> -->
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
                            <td>{{ item.calcDate }}</td>
                            <!--   <td>{{item.paymentsCount}}</td> -->
                             <td>{{ item.started_at }}</td>
                             <td>{{ item.ended_at}}</td>
                         <!--    <td>{{item.payment_type}}</td>
                             <td>{{item.status}}</td>-->
                            <td>
                            <!--    <input  v-model="processed"  v-if="item.process_status == 1" class="form-check-input" type="checkbox" value="1" checked="true" id="checked">
                                <input  v-model="processed"  v-if="item.process_status == null" class="form-check-input" type="checkbox" value="item.phone" id="item.phone">
                                 <button v-if="item.process_status == null" type="button" class="btn btn-outline-info btn-sm">В процессе</button>
                                 -->
                                <input type="checkbox" :value="item.id" id="item.id" class="form-check-input" @change="goProcess(item.id)">
                                <div v-for="status in processedStatus">
                                    <span v-if="item.id == status.subscription_id">
                                        <input v-if="status.process_status == 1" class="form-check-input" type="checkbox" value="1" checked="true" id="checked" @change="unprocess(item.id)">
                                    </span>
                                </div>
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
        }),
        mounted() {
            console.log('Component mounted.');
            this.getList();
            this.getProcessedStatus();
          //  this.getSubscriptionlist();
        },

        created() {
            this.getProductsWithPrices();
        },
        computed: {
            //функция сортировки массива

        },
        methods: {
            getProcessedStatus(){
                axios.post('/reports/get-processed-status', {
                    period: this.period,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59'),
                    product: this.product,
                    type: 8
                })
                    .then(response => {
                        response.data.forEach(elem =>{
                            //console.log(elem.subscription_id);
                            this.processedStatus.push({
                                process_status:elem.process_status,
                                report_type: elem.report_type,
                                subscription_id: elem.subscription_id
                            });

                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            },
            goProcess: function(id) {
                    axios.post('/reports/set-processed-status',{
                        subId: id,
                        report_type: 8,
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
                    report_type: 8,
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
            getList(){
                this.getProcessedStatus();
                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/simple-pay-ends-list', {
                    product: this.product
                })
                    .then(response => {
                        this.spinnerData.loading = false;
                        response.data.forEach(elem =>{

                          //  console.log(elem);
                            var given = moment(elem.ended_at, "YYYY-MM-DD");
                            var current = moment().startOf('day');
                            var diff = moment.duration(given.diff(current)).asDays();


                            this.items.push({
                                id: elem.id,
                                calcDate:  diff,
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
                                paymentsCount: ''
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
