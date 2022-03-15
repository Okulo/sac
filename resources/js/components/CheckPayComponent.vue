<template>
        <div class="row">

            <b-modal id="myModal">
                Статус {{ cpData.Status }} <br>
                Последний платёж {{cpData.LastTransactionDateIso}}<br>
                Следующий платеж {{ cpData.NextTransactionDateIso }}
            </b-modal>


            <div class="col-md-12">
                <h2>Не привязанные платежи</h2>
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
                            <button id="getlist"  @click="getPaylist()"  type="button" class="btn btn-success btn-sm float-right">Получить данные</button>
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

            getPaylist(){

               // $("#exampleModalCenter").modal("show");

               let alldata = [];
                this.spinnerData.loading = true;
                axios.post('/reports/get-pay-list', {
                        period: this.period
                })
                    .then(response => {

                        response.data.forEach(function(item) {

                            console.log(item);
                        })

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
