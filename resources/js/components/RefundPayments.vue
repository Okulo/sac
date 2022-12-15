<template>
    <div class="row">

        <b-modal id="myModal">
            Статус {{ cpData.Status }} <br>
            Последний платёж {{cpData.LastTransactionDateIso}}<br>
            Следующий платеж {{ cpData.NextTransactionDateIso }}
        </b-modal>

        <div class="col-md-12">
            <h2>Возврат средств</h2>
            <div class="card mt-3">
                <div class="card-header">

                    <div class="btn-group d-flex w-100" role="group" aria-label="...">
                        <button type="button" class="btn btn-default w-100" @click="substractDay()">< Назад</button>
                        <div class="w-100" style="border:1px solid #ddd; background-color: #f8f9fa; text-align: center; padding-top: 7px"><b v-if="this.startDate"> {{ getFormattedDate(this.startDate) }}</b><b v-else> {{ this.today }}</b></div>
                        <button type="button" class="btn btn-default w-100" @click="currentDay()">Сегодня</button>
                    </div>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title">Общее количество возвратов<p></p></h5>
                                    <p class="card-text"><h2><b class="text-info">{{pitechItems.length + items.length}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title">Возвраты CloudPayments</h5>
                                    <p class="card-text"><h2><b class="text-indigo">{{items.length}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card" style="height: 8rem">
                                <div class="card-body">
                                    <h5 class="card-title ">Возвраты Pitech</h5>
                                    <p class="card-text "><h2><b class="text-primary">{{pitechItems.length}}</b></h2></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p><br><br></p>
                    <strong>CloudPayments список возвратов</strong><br>

                    <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Транзакция</th>
                            <th scope="col">Услуга</th>
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
<!--                            <td>{{ item.CreatedDate }}</td>-->
                            <td>{{ item.Reason}}</td>
<!--                            <td>{{item.Status}}</td>-->
<!--                            <td>{{ item.StatusCode}}</td>-->
                            <td>{{ item.TransactionId }}</td>
<!--                            <td>{{ item.Refunded }}</td>-->
                            <td>{{ item.Description}}</td>
                            <td>
                                <a target="_blank" :href="'/userlogs?subscription_id=' + item.AccountId">Логи</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <strong>Pitech список возвратов</strong><br>
                    <table class="table">

                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ID</th>
                            <th scope="col">Сумма</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Транзакция</th>
                            <th scope="col">Услуга</th>
                            <td></td>
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="(item, index) in pitechItems" :key="index">
                            <td>{{ index+1 }}</td>
                            <td>{{item.id}}</td>
                            <td>{{ item.amount }}</td>
                            <td>{{ item.message }}</td>
                            <td>{{ item.ordersId}}</td>
                            <td>{{ item.description }}</td>
                            <td>
                                <a target="_blank" :href="'/userlogs?subscription_id=' + item.id">Логи</a>
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
            paysystem: '',
            startDate: '',
            today: moment().locale('ru').format('DD MMMM YY'),
            customerId: null,
            subscriptionId: null,
            filterOpen: false,
            processed: '',
            info: '',
            items: [],
            pitechItems: [],
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
        },
        created() {
            this.currentDay();
        },
        methods: {
            getFormattedDate(date) {
                return moment(date).locale('ru').format("DD MMMM YY")
            },
            currentDay() {
                this.startDate = moment().format('YYYY-MM-DD HH:mm:ss');
                this.getCpRefunds();
                this.getPitechRefunds();
            },
            substractDay() {
                if (this.startDate) {
                    this.startDate = moment(this.startDate).subtract(1, 'day');
                } else {
                    this.startDate = moment().subtract(1, 'day');
                }
                this.getPitechRefunds();
                this.getCpRefunds();
            },
            getCpRefunds() {

                this.items = [];
                this.spinnerData.loading = true;
                axios.post('/reports/get-cp-refunds', {
                    paysystem: this.paysystem,
                    startDate: moment(this.startDate).locale('ru').format('YYYY-MM-DD'),
                })
                    .then(response => {
                        console.log(response.data.Model);
                        response.data.Model.forEach(elem => {
                            if (elem.Type == 1) {
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

                            if (this.items.length < 1) {
                                this.info = 'Нет данных на выбраную дату!';
                                this.spinnerData.loading = false;
                            }

                        });

                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                        Vue.$toast.error('error - ' + error);
                    });
            },
            getPitechRefunds() {

                this.pitechItems = [];
                this.spinnerData.loading = true;
                axios.post('/reports/get-pitech-refunds', {
                    paysystem: this.paysystem,
                    startDate: moment(this.startDate).format('YYYY-MM-DD'),
                    endDate:  moment(this.startDate).format('YYYY-MM-DD'),
                })
                    .then(response => {
                            response.data.content.forEach(elem => {
                                this.info = '';
                                console.log(elem);

                                this.pitechItems.push({
                                    id: elem.extOrdersId,
                                    amount: elem.amount,
                                    description: elem.description,
                                    message: elem.message,
                                    ordersId: elem.ordersId,
                                });
                            });

                        this.spinnerData.loading = false;
                    })
                    .catch(function (error) {
                        console.log('err');
                        console.log(error);
                        Vue.$toast.error('error - ' + error);
                    });
            },
            openModal(customerId, subscriptionId) {
                this.customerId = null;
                this.subscriptionId = null;
                this.customerId = customerId;
                this.subscriptionId = subscriptionId;
                this.$bvModal.show('modal-customer-edit');
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
