<template>
        <div class="row">
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

                        <ul id="example-1">
                            <li v-for="item in items" :key="item.message">
                                {{ item.message }}
                            </li>
                        </ul>

                        <table class="table table-striped table-sm">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">First</th>
                                <th scope="col">Last</th>
                                <th scope="col">Handle</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>Mark</td>
                                <td>Otto</td>
                                <td>@mdo</td>
                            </tr>
                            <tr>
                                <th scope="row">2</th>
                                <td>Jacob</td>
                                <td>Thornton</td>
                                <td>@fat</td>
                            </tr>
                            <tr>
                                <th scope="row">3</th>
                                <td>Larry</td>
                                <td>the Bird</td>
                                <td>@twitter</td>
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
                items: [
                    { message: 'Foo' },
                    { message: 'Bar' }
                ]
            }
        },
        mounted() {
            console.log('Component mounted.')
        }
        ,
        methods: {
            getlist(){
                axios.post('/reports/get-list', {
                    params: {
                        subscription_id: 1234
                    }
                })
                    .then(response => {

                       response.data.forEach(function(item) {


                           // console.log(item.account_id);
                           // console.log(item.request);
                           // console.log(item.notific_id);

                            if(item.Status == 'Declined'){
                                console.log(item.AccountId);
                                console.log(item.Status);
                            }

                       })

                     //   console.log(response.data);


                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            }
        }
    }
</script>
