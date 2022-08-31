<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>


        <div class="col-md-12">
            <h2>Бонусы за неделю {{userNameProp}}</h2>
            <div class="intro">

            </div>
            <div class="card mt-3">
                <div class="card" style="height: 8rem">
                    <div class="card-body">
                        <h5 class="card-title">Всего активных абонементов<p></p></h5>
                        <p class="card-text"><h2><b class="text-teal">{{allActve}}</b></h2></p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="btn-group d-flex w-100" role="group" aria-label="...">
                        <button type="button" class="btn btn-default w-100" @click="substractOneWeek()">< Назад</button>
                        <button type="button" class="btn btn-default w-100" @click="currentWeek()">Текущая неделя</button>
                        <div class="w-100" style="border:1px solid #ddd; background-color: #f8f9fa; text-align: center; padding-top: 7px">

                          <b> c {{modifedDate(weekStart)}}, по {{modifedDate(weekEnd)}}</b>

                        </div>
                    </div>

<p><br></p>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title">Сумма бонусов за неделю<p></p></h5>
                                    <p class="card-text"><h2><b class="text-info">{{summa}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title">Абонементы за неделю (подписки)</h5>
                                    <p class="card-text"><h2><b class="text-indigo">{{count}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title ">Абонементы за неделю (прям перевод)</h5>
                                    <p class="card-text "><h2><b class="text-primary">{{transferCount}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
     export default {
         props: [
             'userIdProp',
             'userNameProp'
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
            summa: '',
            count: '',
            transferCount: '',
            allActve: ''
        }),
        mounted() {
            console.log('Component mounted.');
            this.weekStart =  moment().startOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
            this.weekEnd = moment().endOf('isoWeek').format('YYYY-MM-DD HH:mm:ss');
            this. getAllSubscriptions();
           // this.getList();
            //  this.getSubscriptionlist();
        },
         watch: {
             weekStart: function (val) {
                 //если меняется дата выполняем
                 this.operatorBonuses = [];
                 this.getUserBonus(this.weekStart, this.weekEnd);
                 this.getSubscriptions(this.weekStart, this.weekEnd);
                 this.getTransferCount(this.weekStart, this.weekEnd);
             }
         },
        methods: {
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
            getTransferCount(start, end){
                this.spinnerData.loading = true;
                axios.post('/reports/get-subscriptions',{
                    startDate: start,
                    endDate: end,
                    userId: this.userIdProp,
                    count: 'transfer'
                })
                    .then(response => {
                        // this.getDebtorsList();
                        //console.log(response.data);
                        if(response.data[0].count){
                            this.transferCount = response.data[0].count;
                        }else{
                            this.transferCount = 0;
                        }

                        this.spinnerData.loading = false;

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                    });
            },
            getSubscriptions(start, end){
                this.spinnerData.loading = true;
                axios.post('/reports/get-subscriptions',{
                    startDate: start,
                    endDate: end,
                    userId: this.userIdProp,
                    count: 'subs'
                })
                    .then(response => {
                        // this.getDebtorsList();
                        //console.log(response.data);
                        if(response.data[0].count){
                            this.count = response.data[0].count;
                        }else{
                            this.count = 0;
                        }

                        this.spinnerData.loading = false;

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                    });
            },
            getUserBonus(start, end){
                this.spinnerData.loading = true;
                axios.post('/reports/get-operator-summ',{
                    startDate: start,
                    endDate: end,
                    userId: this.userIdProp
                })
                    .then(response => {
                        // this.getDebtorsList();
                      //console.log(response.data);
                        if(response.data[0].summa){
                            this.summa = response.data[0].summa;
                        }else{
                            this.summa = 0;
                        }

                        this.spinnerData.loading = false;

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                    });
            },
            getAllSubscriptions(){
                this.spinnerData.loading = true;
                axios.post('/reports/get-all-subscriptions',{
                    userId: this.userIdProp
                })
                    .then(response => {
                        // this.getDebtorsList();
                        this.allActve = response.data;
                        // if(response.data[0].summa){
                        //     this.summa = response.data[0].summa;
                        // }else{
                        //     this.summa = 0;
                        // }

                        this.spinnerData.loading = false;

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
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
