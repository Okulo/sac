<template>
    <div class="row">

        <div class="col-md-12">
            <h2>Услуги в архиве</h2>

           <div class="intro">
 Список услуг которые находятся в архиве <p></p>
               При нажатии на название услуги, вы можете ее разархивирврать !
           </div>
            <div class="card mt-3">
                <div class="card-header">

                </div>
                <div class="card-body">
                    <strong>Всего записей -      {{items.length}}</strong><p><br></p>
                    <pulse-loader  class="spinner" style="text-align: center" :loading="spinnerData.loading" :color="spinnerData.color" :size="spinnerData.size"></pulse-loader>

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Название</th>
                            <th scope="col">Описание</th>
                            <th scope="col">Дата арх.</th>
                            <td></td>
                          <!--  <th scope="col"></th> -->
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="(item, index) in items" :key="index">

                            <td>{{ index+1 }}  </td> <!--- {{item.customer_id}} -->
                            <td>
                                <a class="custom-link" role="button" @click="unarchive(item.id)">{{item.title}}</a>
                            </td>
                            <td>{{item.description}}</td>
                            <td>{{ item.deleted_at}}</td>
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
    import CustomerComponent from './CustomerComponent.vue';

    export default {
        components: { CustomerComponent },
        props: [
            'prefixProp',
            'createLinkProp',
        ],
        data: () => ({
            processed: [],
            items: [],
            spinnerData: {
                loading: false,
                color: '#6cb2eb',
                size: '60px',
            },

        }),
        mounted() {
            console.log('Component mounted.');
            this.archivedProductsList();
          //  this.getSubscriptionlist();
        },

        created() {

        },
        computed: {
            //функция сортировки массива

        },
        methods: {

            archivedProductsList(){

                this.items = [];
                // $("#exampleModalCenter").modal("show");
                this.spinnerData.loading = true;
                //  this.spinnerData.loading = true;
                axios.post('/reports/get-archived-products')
                    .then(response => {
                        this.spinnerData.loading = false;

                        response.data.forEach(elem =>{
                            //   console.log(elem.data);
                          //  var given = moment(elem.ended_at, "YYYY-MM-DD");

                            this.items.push({
                                id: elem.id,
                                code: elem.code,
                                title: elem.title,
                                description: elem.description,
                                deleted_at: elem.deleted_at,
                            });


                        });

                    })
                    .catch(function (error) {
                        console.log(error);
                        Vue.$toast.error('error - '+ error);
                    });
            },
            unarchive(id){
                if (confirm('Разархивировать?')) {
                    $.ajax({
                        url: "/products/restore-product",
                        // dataType: "json", // Для использования JSON формата получаемых данных
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        method: "POST", // Что бы воспользоваться POST методом, меняем данную строку на POST
                        data: {
                            id: id
                        },
                        success: function(data) {
                            window.location.reload();
                        },
                        error: function(data) {
                            console.log(data);
                            alert('Ошибка!'); // Возвращаемые данные выводим в консоль
                        }
                    });
                }
            }
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
