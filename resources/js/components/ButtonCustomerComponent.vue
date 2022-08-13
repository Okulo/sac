<template>
    <div>
        <a v-b-modal.modal-customer-create
            class="btn btn-info text-white" title="Добавить">
            <i class="fa fa-plus"></i>
        </a>
        <customer-component type-prop="create" :user-id-prop="userIdProp" :customer-id-prop="'create'"></customer-component>
    </div>
</template>

<script>
    export default {
        props: [
            'userIdProp',
        ],
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
