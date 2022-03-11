<template>
        <div class="row">
            <!-- Modal -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ...
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="card mt-3">
                    <div class="card-header">
                        <div data-v-754b2df6="" class="input-group">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="customRadioInline1" name="customRadioInline1" class="custom-control-input" checked>
                                <label class="custom-control-label" for="customRadioInline1">За сутки</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="customRadioInline2" name="customRadioInline1" class="custom-control-input">
                                <label class="custom-control-label" for="customRadioInline2">За неделю</label>
                            </div>
                            <button id="getlist"  @click="getlist()"  type="button" class="btn btn-success btn-sm float-right">Получить данные</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th scope="col">Аккаунт ID</th>
                                <th scope="col">Дата </th>
                                <th scope="col">Статус CP</th>
                                <th scope="col">Причина</th>
                                <th scope="col">Статус подписки</th>
                                <th scope="col">Подписка ID</th>
                                <th>CP</th>
                            </tr>
                            </thead>
                            <tbody v-for="item in items">
                            <tr>
                                <th scope="row">  {{item.account_id}}</th>
                                <td>{{ item.date_time }}</td>
                                <td>{{ item.pay_status }}</td>
                                <td>{{item.reason}}</td>
                                <td>{{item.subscription_status}}</td>
                                <td>{{item.subscription_id}}  <a v-if="item.subscription_id" href="#"><i class="fa fa-info-circle" style="color: #1B96FE"></i></a></td>
                                <td>         <a class="custom-link" role="button" @click="getCpData(item.subscription_id)">СP</a></td>
                            </tr>
                            </tbody>
                        </table>


                    </div>
                </div>
            </div>
        </div>
</template>


<script>
    export default {
        data() {
            return {
                items: [],
                spinnerData: {
                    loading: false,
                    color: '#6cb2eb',
                    size: '60px',
                },
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
        ,
        methods: {
            getlist(){

               // $("#exampleModalCenter").modal("show");

               let alldata = [];
                this.spinnerData.loading = true;
                axios.post('/reports/get-list', {
                    params: {
                        subscription_id: 1234
                    }
                })
                    .then(response => {

                        this.items = alldata;

                      //  console.log(response.data);
                        this.spinnerData.loading = false;
                        response.data.forEach(function(item) {

                            //alldata.push(item.subscription);

                            alldata.push({
                                account_id: item.request.AccountId,
                                pay_status: item.request.Status,
                                reason: item.request.Reason,
                                subscription_id: item.request.SubscriptionId,
                                subscription_status: item.subscription.status,
                                date_time: item.request.DateTime

                            });

                            // console.log(item.request.AccountId);
                            // console.log(item.subscription.reason_id);
                            // console.log(item.request.Status);
                            // console.log(item.request.Reason);
                            // console.log(item.request.SubscriptionId);
                            // console.log('\n');


                       })

                     //   console.log(response.data);


                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });

                console.log(this.items);
            },
            openModal(customerId, subscriptionId) {
                this.customerId = null;
                this.subscriptionId = null;
                this.customerId = customerId;
                this.subscriptionId = subscriptionId;
                // this.$refs['modal-customer'].show()
                this.$bvModal.show('modal-customer-edit');
            },
            getCpData(id){

                axios.post('/reports/getSubscription', {
                    id: id
                })
                    .then(response => {
                        console.log('getCpData');
                        console.log(response.data.Success);
                        console.log(response.data.Model);
                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error(' '+error);
                    });
            },
        }
    }
</script>
