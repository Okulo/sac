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
                        <div data-v-754b2df6="" class="input-group">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input v-model="period"  type="radio" value="day" id="period" class="custom-control-input" checked>
                                <label class="custom-control-label" for="period">За неделю</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input v-model="period" type="radio" value="week" id="priod2" class="custom-control-input">
                                <label class="custom-control-label" for="priod2">За все время</label>
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
                                <th scope="col">Дата</th>
                                <th scope="col">Имя</th>
                                <th scope="col">Тел.</th>
                                <th scope="col">ID</th>
                                <th scope="col">Сумма </th>
                                <th scope="col">Статус</th>
                                <th scope="col">Пользватель</th>
                            </tr>
                            </thead>
                            <tbody v-for="item in items">
                            <tr>
                                <td>{{item.created_at}}</td>
                                <th scope="row">{{item.name}}</th>
                                <th scope="row">{{item.phone}}</th>
                                <th scope="row">{{item.subscription_id}}</th>
                                <td>{{ item.amount}}</td>
                                <td>{{ item.status }}</td>
                                <td>{{ item.customer_id}}</td>
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

                let array = [];
              //  this.spinnerData.loading = true;
                axios.post('/reports/get-pay-list', {
                        period: this.period
                })
                    .then(response => {

                        response.data.forEach(elem =>{
                           // console.log(elem.data);

                                this.items.push({
                                    created_at: elem.created_at,
                                    subscription_id: elem.id,
                                    amount: elem.price,
                                    status: elem.status,
                                    customer_id: elem.customer_id,
                                    name: elem.name,
                                    phone: elem.phone,
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
