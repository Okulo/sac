<template>
        <div class="row">

            <b-modal id="myModal">
                Статус {{ cpData.Status }} <br>
                Последний платёж {{cpData.LastTransactionDateIso}}<br>
                Следующий платеж {{ cpData.NextTransactionDateIso }}
            </b-modal>


            <div class="col-md-12">
                <h2>Список отказавшихся</h2>
                <div class="card mt-3">
                    <div class="card-header">

                        <div class="col-md-12">
                                <datetime
                                    type="date"
                                    v-model="startDate"
                                    input-class="form-control"
                                    valueZone="Asia/Almaty"
                                    value-zone="Asia/Almaty"
                                    zone="Asia/Almaty"
                                    :auto="true"
                                ></datetime>

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

                        <button id="getlist" v-if="startDate && endDate"  @click="getlist()"  type="button" class="btn btn-success btn-sm float-right">Получить данные</button>

                    </div>
                    <div class="card-body">
                        <strong>Всего записей -      {{items.length}}</strong><p><br></p>
                        <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Имя</th>
                                <th scope="col">Тел.</th>
                                <th scope="col">Тип оплаты</th>
                                <th scope="col">Статус</th>
                                <th scope="col">Дата отказа</th>
                                <th scope="col">Причина</th>
                                <th scope="col">Оконч. абон</th>
                                <th scope="col"></th>
                            </tr>
                            </thead>

                            <tbody v-for="item in items">
                            <tr>
                                <td>{{ item.id }}</td>
                                <th>{{item.name}}</th>
                                <th>{{item.phone}}</th >
                                <td>Прямой перевод</td>
                                <td>Отказался</td>
                                <td>{{ item.updated_at}}</td>
                                <td>{{item.reason}}</td >
                                <td>{{ item.ended_at }}</td>
                                <td>    <a target="_blank" :href="'/userlogs?subscription_id=' + item.id">Логи абон.</a></td>

                            </tr>
                            </tbody>
                        </table>


                    </div>
                </div>
            </div>
        </div>
</template>

<script>
    import moment from 'moment';
    export default {
        data: () => ({
                startDate: '',
                endDate: '',
                customerId: null,
                subscriptionId: null,
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
            console.log('Component mounted.')
        },
        computed: {
            //функция сортировки массива

        },
        methods: {
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
    .Active {
        color: #00d25c;
    }
    .Rejected{
        color: #d32535;
    }
</style>
