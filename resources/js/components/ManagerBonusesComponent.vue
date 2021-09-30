<template>
    <div class="container-fluid">
        <form :action="route">
            <div>
                <div class="card mb-2" id="filter">
                    <div 
                        style="line-height: 25px; cursor: pointer;" 
                        @click="filterOpen = !filterOpen" 
                        class="card-header py-1"
                    >
                        Фильтр
                        <small class="float-right">
                            <a id="filter-toggle" class="btn btn-default btn-sm" title="Скрыть/показать">
                                <i class="fa fa-toggle-off " :class="{'fa-toggle-on': filterOpen}"></i>
                            </a>
                        </small>
                    </div>
                    <div class="card-body" v-show="filterOpen" :class="{slide: filterOpen}">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="started_at" class="col-form-label">С</label>
                                    <datetime
                                        type="date"
                                        v-model="data.from"
                                        input-class="form-control"
                                        valueZone="Asia/Almaty"
                                        value-zone="Asia/Almaty"
                                        zone="Asia/Almaty"
                                        format="dd LLLL"
                                        :auto="true"
                                    ></datetime>
                                    <input type="hidden" name="from" :value="convertDate(data.from, 'YYYY-MM-DD')">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="started_at" class="col-form-label">По</label>
                                    <datetime
                                        type="date"
                                        v-model="data.to"
                                        input-class="form-control"
                                        valueZone="Asia/Almaty"
                                        value-zone="Asia/Almaty"
                                        zone="Asia/Almaty"
                                        format="dd LLLL"
                                        :auto="true"
                                    ></datetime>
                                    <input type="hidden" name="to" :value="convertDate(data.to, 'YYYY-MM-DD')">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="started_at" class="col-form-label">Выберите период</label>
                                    <select v-model="data.period" name="period" class="form-control">
                                        <option v-for="(option, optionIndex) in periods" :key="optionIndex" :value="optionIndex">{{ option }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button style="margin-bottom: 15px;" type="submit" class="btn btn-primary">Перейти</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <highcharts 
                        :options="chart"
                        :updateArgs="[true, true, true]"
                        :ref="'chart'"
                    ></highcharts>
                </div>
                <div class="card">
                    <div style="margin-left: 20px; margin-top: 20px; margin-bottom: 30px; margin-right: 0px;">
                        <h1 style="text-align: center">{{ getTitle() }}</h1>
                        <div v-for="(usersProducts, productIndex) in managerBonusesGroupByProducts[data.currentPoint]" :key="productIndex">
                            <h3 style="margin-bottom: 20px">{{ products[productIndex] }} - {{ managerBonusesGroupByProducts[data.currentPoint][productIndex] }}₸</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
import {Chart} from 'highcharts-vue'

export default {
    props: [
        'routeProp',
        'dataProp',
        'productsProp',
        'periodsProp',
        'chartProp',
        'managerBonusesGroupByProductsProp'
    ],
    components: {
        highcharts: Chart,
    },
    data() {
        return {
            managerBonusesGroupByProducts: this.managerBonusesGroupByProductsProp,
            route: this.routeProp,
            records: this.recordsProp,
            chart: this.chartProp,
            filterOpen: false,
            products: this.productsProp,
            periods: this.periodsProp,
            data: {
                from: moment(this.dataProp.from).tz('Asia/Almaty').format(),
                to: moment(this.dataProp.to).tz('Asia/Almaty').format(),
                period: this.dataProp.period,
                currentPoint: this.dataProp.currentPoint,
                lastPoint: this.dataProp.lastPoint,
            },
        }
    },
    beforeMount() {
        this.chart.xAxis.labels = {
            formatter: function(value) {
                return moment(value.value).tz('Asia/Almaty').locale("ru").format('D MMM');
            }
        };

        this.chart.plotOptions.series = {
            cursor: 'pointer',
            point: {
                events: {
                    click: (e) => {
                        this.pointClick(e);
                    }
                }
            }
        };
    },
    methods: {
        getTitle() {
            let end = moment.unix(this.data.currentPoint / 1000).locale("ru").format('LL');
            let start = moment.unix(this.data.currentPoint / 1000).weekday(-6).locale("ru").format('D');
            return  start +' - ' + end;
        },
        pointClick(e) {
            this.total = 0;
            this.data.currentPoint = e.point.category;
            this.data.lastPoint = e.point.category - 604800000;
        },
        convertDate(date, format) {
            return moment(date).tz('Asia/Almaty').lang("ru").format(format);
        },
    }
}
</script>

<style scoped>
.v-spinner {
    width: 100%;
    height: 100%;
    text-align: center;
    position: absolute;
    background: #00000017;
}
.last-polzunok {
    background-color: rgb(251, 211, 98) !important;
    text-align: right;
}
.record-polzunok {
    background-color: #d0d0d0 !important;
    text-align: right;
}
.progress-bar {
    position: absolute;
    height: 100%;
    overflow: unset;
}
.progress {
    position: relative;
    overflow: unset;
}
.progress-value {
    position: relative;
    font-size: 14px;
    font-weight: bold;
    display: block;
    float: right;
    text-align: right;
    width: 100%;
    height: 100%;
}
.record-value::after {
    content: "";
    background: #d0d0d0;
    position: absolute;
    top: 0;
    width: 3px;
    right: 0px;
    height: 55px;
}
.record-span {
    position: absolute;
    right: 7px;
    top: 50px;
    color: #7b7b7b;
}

.last-value::after {
    content: "";
    background: #fbd362;
    position: absolute;
    width: 3px;
    bottom: 0;
    right: 0px;
    height: 55px;
}
.last-span {
    position: absolute;
    right: 7px;
    top: -23px;
    color: rgb(247 183 3);
}
</style>