<template>
    <div>
        <div v-if="this.series.length > 0 && errors.length === 0" id="chart">
            <vue-apex-charts type="area" height="350" :options="computedChartOptions"
                             :series="series"></vue-apex-charts>
        </div>
        <div v-else class="text-lg font-light leading-relaxed p-3">
            No Data For <b>{{ this.title }}</b>
            <p class="text-red-600 font-semibold text-sm pt-2" v-for="error in errors">{{ error }}</p>
        </div>
    </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts';

export default {
    inject: ["eventHub"],
    components: {
        VueApexCharts,
    },
    data() {
        return {
            chartOptions: {
                chart: {
                    height: 350,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                colors: ['#F12007', '#47DF11'],
                xaxis: {
                    type: 'datetime',
                    categories: []
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                },
            }
        }
    },
    props: {
        series: {
            default: Array,
        },
        xname: {
            default: '',
        },
        yname: {
            default: '',
        },
        title: {
            default: ''
        },
        categories: {
            default: Array,
        },
        errors: {
            default: Array
        }
    },
    computed: {
        computedChartOptions() {
            return {
                ...this.chartOptions, ...{
                    xaxis: {
                        title: {
                            text: this.xname,
                        },
                        categories: this.categories,
                        tickPlacement: 'on'
                    },
                    title: {
                        text: this.title,
                        align: 'center',
                        margin: 50,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: undefined,
                            color: '#263238'
                        },
                    },
                    yaxis: {
                        title: {
                            text: this.yname,
                        },
                        style: {
                            fontSize: "15px",
                        }
                    },
                }
            };
        },
    }
}
</script>
