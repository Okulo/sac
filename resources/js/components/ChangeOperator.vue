<template>
    <div class="row">
        <div class="col-md-12">
            <h2>Замена оператора</h2>

            <div class="intro">
                После замены оператора, вы будете переведены на страницу абонементов.
                <p></p>
                Запомните пожалуйтм абонимент в котором сменили оператора!
            </div>
            <div class="card mt-3">
                <div class="card-body">
                    <form>
                        Абонемент
                        <hr>
                        <div class="row">
                            <div class="col-2">
                                <label for="subid">ID абонемента</label>
                                <input type="text" disabled="true" class="form-control" id="subid" :value="idProp">
                            </div>
                            <div class="col-4">
                                <label for="subname">Услуга</label>
                                <input type="text" disabled="true" class="form-control" id="subname" :value="this.subscription.title">
                            </div>
                            <div class="col-3">
                                <label for="subname2">Клиент</label>
                                <input type="text" disabled="true" class="form-control" id="subname2" :value="this.subscription.customer_name">
                            </div>
                            <div class="col-3">
                                <label for="subname3">Телефон</label>
                                <input type="text" disabled="true" class="form-control" id="subname3" :value="this.subscription.phone">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <label for="oper">Текущий оператор</label>
                                <input type="text" disabled="true" class="form-control" id="oper" :value="this.subscription.name">
                            </div>

                        <div class="col-6">
                            <label for="exampleFormControlSelect1">Выбрать нового оператора</label>
                            <select class="form-control" id="exampleFormControlSelect1">
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                            </select>
                        </div>
                        </div>
                        <p><br></p>
                        <button type="button" class="btn btn-primary btn-sm float-right">Сохранить изменения</button>
                    </form>
                </div>
            </div>
        </div>
        </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-2'
import { defaultExtensions } from '@tiptap/starter-kit'
import moment from "moment";

export default {
    props: [
        'idProp',
        ],
    data: () => ({
        subscription: '',
        users: []
    }),
  mounted() {
        this.getSubscriptionDetail();
  },
    methods: {
        getSubscriptionDetail(){
            axios.post('/subscriptions/getDetail', {
                id: this.idProp,
            })
                .then(response => {
                  // console.log(response.data[0]);
                        this.subscription = response.data[0];
                })
                .catch(function (error) {
                    console.log(error);
                    Vue.$toast.error('error - '+ error);
                });
        },

        getUserList(){
            axios.get('/users/list', {
                reportType: 11
            })
                .then(response => {

                    console.log(response);
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
    },
}
</script>
