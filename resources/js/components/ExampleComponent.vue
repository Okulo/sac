<template>
        <div class="row">

            <b-modal id="myModal">
                Статус {{ cpData.Status }} <br>
                Последний платёж {{cpData.LastTransactionDateIso}}<br>
                Следующий платеж {{ cpData.NextTransactionDateIso }}
            </b-modal>


            <div class="col-md-12">
                <h2>Ошибки оплаты</h2>
                <div class="card mt-3">
                    <div class="card-header">
                        <div data-v-754b2df6="" class="input-group">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input v-model="period"  type="radio" value="day" id="period" class="custom-control-input" checked>
                                <label class="custom-control-label" for="period">За сегодня</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input v-model="period" type="radio" value="week" id="priod2" class="custom-control-input">
                                <label class="custom-control-label" for="priod2">За неделю</label>
                            </div>
                            <button id="getlist"  @click="getlist()"  type="button" class="btn btn-success btn-sm float-right">Получить данные</button>
                            &nbsp &nbsp &nbsp
                            <button id="getCpStatus" v-if="cp"  @click="getCpStatus()"  type="button" class="btn btn-info btn-sm float-right">Получить статусы подпски</button>
                        </div>

                    </div>
                    <div class="card-body">
                        <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th scope="col">Клиент</th>
                                <th scope="col">Телефон</th>
                                <th scope="col">Дата </th>
                                <th scope="col">Причина</th>
                                <th scope="col">Статус абон</th>
                                <th scope="col">Подписка ID</th>
                                <th scope="col">Статус подписки</th>
                                <th>CP</th>
                            </tr>
                            </thead>
                            <ul id="example-1">
                                <li v-for="item in cpStatus" >
                                    {{ item.cp_status }}
                                </li>
                            </ul>
                            <tbody v-for="item in items">
                            <tr v-bind:class="item.cp_status">
                                <td>{{item.account_id}}</td>
                                <th scope="row">{{item.customer.name}}</th>
                                <td>{{ item.customer.phone}}</td>
                                <td>{{ item.date_time }}</td>
                                <td>{{ item.reason }}</td>
                                <td>{{ item.subscription_status }}</td>
                                <td>{{ item.subscription_id }}</td>
                                <td>{{ item.cp_status }}</td>
                                <td><a class="custom-link" v-b-modal="'myModal'" role="button" @click="getCpData(item.subscription_id)">СP</a></td>
                            </tr>
                            </tbody>
                        </table>


                    </div>
                </div>
                <div>

                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action ">
                            <h5>Справочник статусов подписки</h5>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action">
                            <b>Active</b> - Подписка активна - После создания и очередной успешной оплаты
                        </button>
                        <button type="button" class="list-group-item list-group-item-action">
                            <b>PastDue</b> - Просрочена - После одной или двух подряд неуспешных попыток оплаты
                        </button>
                        <button type="button" class="list-group-item list-group-item-action">
                            <b>Cancelled</b> - Отменена - В случае отмены по запросу
                        </button>
                        <button type="button" class="list-group-item list-group-item-action">
                            <b>Rejected</b> - Отклонена - В случае трех неудачных попыток оплаты, идущих подряд
                        </button>
                        <button type="button" class="list-group-item list-group-item-action">
                           <b>Expired</b> - Завершена - В случае завершения максимального количества периодов (если были указаны)
                        </button>

                    </div>

                </div>
            </div>
        </div>
</template>

<script>

    import moment from 'moment';

    export default {
        data: () => ({
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
                period: 'day'
        }),
        mounted() {
            console.log('Component mounted.')
        }
        ,
        methods: {
            sendInfo(item) {
                this.selectedUser = item;
            },

            getlist(){

               // $("#exampleModalCenter").modal("show");

               let alldata = [];
                this.spinnerData.loading = true;
                axios.post('/reports/get-list', {
                        period: this.period
                })
                    .then(response => {

                        this.items = alldata;
                        this.cp = true;
                      //  console.log(response.data);
                        this.spinnerData.loading = false;
                        response.data.forEach(function(item) {

                            alldata.push({
                                account_id: item.request.AccountId,
                                pay_status: item.request.Status,
                                reason: item.request.Reason,
                                subscription_id: item.request.SubscriptionId,
                                subscription_status: item.subscription.status,
                                date_time:  moment(item.request.DateTime).add(6,'hours').format('YYYY-MM-DD, hh:mm:ss'),
                                customer: item.customer,
                                cp_status: ''

                            });
                       })

                     //   console.log(response.data);

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
