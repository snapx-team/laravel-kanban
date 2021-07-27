<template>
    <div>
        <div v-if="this.series.length > 0" id="chart">
            <vue-apex-charts type="bar" height="350" :options="computedChartOptions"
                             :series="computedSeries"></vue-apex-charts>
        </div>
        <div v-if="this.series.length === 0">
            No Data for {{ this.title }}
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
            series: [{
                name: '',
                data: []
            }],
            chartOptions: {
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        columnWidth: '60%',
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2
                },
                grid: {
                    row: {
                        colors: ['#fff', '#f2f2f2']
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: "horizontal",
                        shadeIntensity: 0.25,
                        gradientToColors: undefined,
                        inverseColors: true,
                        opacityFrom: 0.85,
                        opacityTo: 0.85,
                        stops: [50, 0, 100]
                    },
                },
            }
        }
    },
    props: {
        data: {
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
        }
    },
    computed: {
        computedChartOptions() {
            return {
                ...this.chartOptions, ...{
                    xaxis: {
                        title: {
                            text: this.yname,
                        },
                        labels: {
                            rotate: -45
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
                            text: this.xname,
                        },
                    },
                }
            };
        },
        computedSeries() {
            return [{
                name: this.xname,
                data: this.data
            }]
        }
    }
}
</script>
