<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Продажи операторов</h2>
            <div class="intro">

            </div>
            <div class="card mt-3">
                <div class="card-header">
                    <b>Выберите дату</b> (по умолчанию выводится инфо за последний месяц)
                    <div class="row" style="padding-top: 20px; ">
                        <div class="col-3">
                            С
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
                            По
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
                            &nbsp<br>
                            <button id="getlist" @click="showSales()"  type="button" class="btn btn-success btn-sm">Получить данные</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div class="card col-4"  v-for="group, userName in groups">
                        <div class="card-header"  >
                            <b>{{userName}}</b>
                        </div>
                        <ul class="list-group list-group-flush" v-for="item, index in group">
                            <li class="list-group-item"> {{item.payType}} -  {{item.summa}}</li>
                        </ul>
                    </div>


                </div>
            </div>
            <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>
        </div>
        <customer-component type-prop="edit" :subscription-id-prop="subscriptionId" :customer-id-prop="customerId"></customer-component>

    </div>
</template>

<script>
    import moment from 'moment';
     export default {
        data: () => ({
            sales: [],
            startDate: '',
            endDate: '',
            customerId: null,
            subscriptionId: null,
            filterOpen: false,
            processed: '',
            items: [],
            processedStatus: [],
            operatorBonuses: [],
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
            users: [],
            weekStart: '',
            weekEnd: '',
            type: ''
        }),
        mounted() {
            console.log('Component mounted.');
            this.weekStart =  moment().startOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
            this.weekEnd = moment().endOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
           // this.getList();
            this.getProcessedStatus();
            //  this.getSubscriptionlist();
        },
         computed: {
             groups(){
                 return this.groupBy(this.sales, 'name')
             }
         },
        created() {
            this.getProductsWithPrices();
           // this.getUserList();

        },
         watch: {
             weekStart: function (val) {
                 //если меняется дата выполняем
                 this.operatorBonuses = [];
                 this.getUserBonus(this.weekStart, this.weekEnd);
             }
         },
        methods: {
            showSales(){
                this.spinnerData.loading = true;
                axios.post('/reports/get-sales', {
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59')
                })
                    .then(response => {
                        response.data.forEach(elem =>{
                           // console.log(elem);

                            switch (elem.type) {
                                case "tries":
                                    this.type = "Пробует бесплатно";
                                    break;
                                case "transfer":
                                    this.type = "Прямой перевод";
                                    break;
                                case "cloudpayments":
                                    this.type = "Подписка Cloudpayment";
                                    break;
                                case "simple_payment":
                                    this.type = "Разовое списание";
                                    break;
                                case "pitech":
                                    this.type = "Подписка Pitech";
                                    break;
                                default:
                                    this.type = ""
                            }

                            this.sales.push({
                                name: elem.name,
                                summa: elem.summa,
                                user_id: elem.user_id,
                                payType: this.type
                            });

                        });
                        this.spinnerData.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            },
              groupBy(array, key){
                const result = {}
                array.forEach(item => {
                    if (!result[item[key]]){
                        result[item[key]] = []
                    }
                    result[item[key]].push(item)
                })
                return result
                  },
            modifedDate(date){
                return moment(date).locale('ru').format('DD MMM ');
            },
            substractOneWeek(){
                    this.weekStart =  moment(this.weekStart).subtract(1, 'weeks').startOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
                    this.weekEnd = moment(this.weekEnd ).subtract(1, 'weeks').endOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
            },
            currentWeek(){
                this.weekStart =  moment().startOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
                this.weekEnd = moment().endOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
            },
            getUserBonus(start, end){
                this.spinnerData.loading = true;
                axios.post('/reports/get-user-bonus',{
                    startDate: start,
                    endDate: end
                })
                    .then(response => {
                        // this.getDebtorsList();
                      //console.log(response.data);

                        response.data.forEach(elem =>{
                            this.operatorBonuses.push({
                                name: elem.name,
                                summa: elem.summa,
                                user_id: elem.user_id
                            });
                        })

                        this.spinnerData.loading = false;

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                    });
            },
            saveStatus(id){

                if( $(".status-"+id).val()){
                    var val = $(".status-"+id).val();

                    axios.post('/reports/save-status',{
                        subId: id,
                        status: val
                    })
                        .then(response => {
                            // this.getDebtorsList();
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
                    report_type: 11,
                    status: 1
                })
                    .then(response => {
                        // this.getDebtorsList();
                        Vue.$toast.success('Статус успешно изменен');

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
                    report_type: 11,
                    status: 0
                })
                    .then(response => {
                        // this.getDebtorsList();
                        Vue.$toast.success('Статус успешно изменен');
                        console.log('unprocess');
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
                    reportType: 11
                })
                    .then(response => {
                        response.data.data.forEach(elem =>{
                            if(elem.is_active.value == 'Активный'){
                              //  console.log(elem);
                                this.users.push({
                                    id: elem.id.value,
                                    account: elem.account.value,
                                    email: elem.email.value,
                                    name: elem.name.value,
                                    role_id: elem.role_id.value
                                });
                            }

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
            getProcessedStatus(){
                axios.post('/reports/get-processed-status', {
                    period: this.period,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD 00:00:01'),
                    endDate: moment(this.endDate).locale('ru').format('YYYY-MM-DD 23:59:59'),
                    product: this.product,
                    type: 11
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
