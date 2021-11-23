<template>
    <div>
        <backlog-bar :name="'Metrics'"></backlog-bar>
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>
        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <div class="flex-1 space-y-2">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Start</span>
                <date-picker type="date" v-model="start"
                             placeholder="YYYY-MM-DD"
                             format="YYYY-MM-DD"
                ></date-picker>
                <div class="flex-1 space-y-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">End</span>
                    <date-picker type="date" v-model="end"
                                 placeholder="YYYY-MM-DD"
                                 format="YYYY-MM-DD">
                    </date-picker>
                </div>
            </div>

            <button @click="filter()"
                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">
                <span>Filter</span>
            </button>
        </div>

        <div class="p-10">

            <div class="grid grid-cols-12 gap-4">

                <div class="lg:col-span-4 col-span-12">
                    <div @click="showTicketBreakdown()"
                         :class="{'bg-gray-200': showTB}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Ticket Breakdown</span>
                    </div>

                    <div @click="showTicketEmployee()"
                         :class="{'bg-gray-200': showTE}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Ticket Employee Breakdown</span>
                    </div>

                    <div @click="showTicketStats()"
                         :class="{'bg-gray-200': showTS}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Ticket Statistics</span>
                    </div>

                    <div @click="showHourlyStats()"
                         :class="{'bg-gray-200': showHS}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Hourly Statistics</span>
                    </div>

                    <div @click="showCreatedVsResolved()"
                         :class="{'bg-gray-200': showCR}"
                         class="flex items-center px-3 rounded my-2 border hover:bg-gray-200 py-2 cursor-pointer">
                        <span class="font-medium text-gray-900 pl-2">Created Vs Resolved</span>
                    </div>
                </div>

                <div class="lg:col-span-8 col-span-12">
                    <div v-if="chartIsLoading" class="m-2 rounded bg-blue-50 flex flex-col items-center bg-blue-50 h-full">
                        <loading-animation :size="80" class="m-auto"></loading-animation>
                    </div>
                    <div v-else>
                        <div v-if="showTB" class="border rounded my-2">
                            <pie-chart
                                v-if="badgeData != null"
                                :series="badgeData.hits"
                                :labels="badgeData.names"
                                :title="'Tickets by Badge'">
                            </pie-chart>
                        </div>
                        <div v-if="showTE" class="border rounded my-2 ">
                            <pie-chart
                                v-if="ticketByEmployeeData != null"
                                :series="ticketByEmployeeData.hits"
                                :labels="ticketByEmployeeData.names"
                                :title="'Tickets Created by Employee'">
                            </pie-chart>
                        </div>
                        <div v-if="showTB" class="border rounded my-2 ">
                            <pie-chart
                                v-if="contractData != null"
                                :series="contractData.hits"
                                :labels="contractData.names"
                                :title="'Tickets by Contract'">
                            </pie-chart>
                        </div>
                        <div v-if="showTE" class="border rounded my-2 ">
                            <pie-chart
                                v-if="closedTasksByEmployee != null"
                                :series="closedTasksByEmployee.hits"
                                :labels="closedTasksByEmployee.names"
                                :title="'Tickets Closed by Assigned Employee'">
                            </pie-chart>
                        </div>
                        <div v-if="showTE" class="border rounded my-2 ">
                            <pie-chart
                                v-if="closedTasksByAdmin != null"
                                :series="closedTasksByAdmin.hits"
                                :labels="closedTasksByAdmin.names"
                                :title="'Tickets Closed by Admin'">
                            </pie-chart>
                        </div>
                        <div v-if="showTS" class="border rounded my-2 ">
                            <bar-chart
                                v-if="creationByHour != null"
                                :data="creationByHour.hits"
                                :categories="creationByHour.names"
                                :xname="'Tickets'"
                                :yname="'Hour'"
                                :title="'Tickets by Hour Created'">
                            </bar-chart>
                        </div>
                        <div v-if="showHS" class="border rounded my-2 ">
                            <bar-chart
                                v-if="delayByBadge != null"
                                :data="delayByBadge.hits"
                                :categories="delayByBadge.names"
                                :xname="'Hours'"
                                :yname="'Categories'"
                                :title="'Average Hours by Badge'">
                            </bar-chart>
                        </div>
                        <div v-if="showHS" class="border rounded my-2 ">
                            <bar-chart
                                v-if="delayByEmployee != null"
                                :data="delayByEmployee.hits"
                                :categories="delayByEmployee.names"
                                :xname="'Hours'"
                                :yname="'Employees'"
                                :title="'Average Hours by Employee'">
                            </bar-chart>
                        </div>
                        <div v-if="showCR" class="border rounded my-2 ">
                            <line-chart
                                v-if="createdVsResolved != null"
                                :series="createdVsResolved.series"
                                :categories="createdVsResolved.categories"
                                :xname="'Days'"
                                :yname="'Total Tickets'"
                                :title="'Created Vs Resolved'">
                            </line-chart>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import PieChart from "./metricsComponents/PieChart.vue";
import BarChart from "./metricsComponents/BarChart.vue";
import LineChart from "./metricsComponents/LineChart.vue";
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue";
import LoadingAnimation from "../global/LoadingAnimation";

export default {
    inject: ["eventHub"],
    components: {
        PieChart,
        BarChart,
        LineChart,
        BacklogBar,
        LoadingAnimation
    },
    data() {
        return {
            chartIsLoading: false,
            badgeData: null,
            ticketByEmployeeData: null,
            creationByHour: null,
            contractData: null,
            closedTasksByEmployee: null,
            closedTasksByAdmin: null,
            delayByBadge: null,
            delayByEmployee: null,
            createdVsResolved: null,
            start: this.getMonday(new Date()),
            end: new Date(),
            showTB: true,
            showTE: false,
            showTS: false,
            showHS: false,
            showCR: false,
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.start.setDate(this.start.getDate() - (this.start.getDay() + 6) % 7);
        this.reload();
    },
    computed: {
        startTime() {
            return this.start.toISOString().slice(0, 10);
        },
        endTime() {
            return this.end.toISOString().slice(0, 10);
        }
    },
    methods: {
        reload() {
            if (this.showTB) {
                this.showTicketBreakdown();
            }
            if (this.showTE) {
                this.showTicketEmployee()
            }
            if (this.showTS) {
                this.showTicketStats();
            }
            if (this.showHS) {
                this.showHourlyStats()
            }
            if (this.showCR) {
                this.showCreatedVsResolved();
            }

        },
        showTicketBreakdown() {
            this.chartIsLoading = true;
            this.showTB = true;
            this.showTE = false;
            this.showTS = false;
            this.showHS = false;
            this.showCR = false;

            Promise.all([this.getBadgeData(), this.getContractData()]).then(() => {
                this.chartIsLoading = false;
            });
        },
        showTicketEmployee() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.showTE = true;
            this.showTS = false;
            this.showHS = false;
            this.showCR = false;

            Promise.all([this.getTicketsByEmployee(), this.getClosedTasksByEmployee(), this.getClosedTasksByAdmin()]).then(() => {
                this.chartIsLoading = false;
            });
        },
        showTicketStats() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.showTE = false;
            this.showTS = true;
            this.showHS = false;
            this.showCR = false;

            Promise.all([this.getCreationByHour()]).then(() => {
                this.chartIsLoading = false;
            });
        },
        showHourlyStats() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.showTE = false;
            this.showTS = false;
            this.showHS = true;
            this.showCR = false;

            Promise.all([this.getDelayByBadge(), this.getDelayByEmployee()]).then(() => {
                this.chartIsLoading = false;
            });
        },
        showCreatedVsResolved() {
            this.chartIsLoading = true;
            this.showTB = false;
            this.showTE = false;
            this.showTS = false;
            this.showHS = false;
            this.showCR = true;

            Promise.all([this.getCreatedVsResolved()]).then(() => {
                this.chartIsLoading = false;
            });

        },
        getMonday(d) {
            d = new Date(d);
            let day = d.getDay(),
                diff = d.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        },
        filter() {
            if (this.start === null || this.start === '' || this.end === null || this.end === '') {
                alert("fill date fields");
            } else {
                this.reload();
            }
        },
        async getBadgeData() {
            return await this.asyncGetBadgeData(this.startTime, this.endTime).then((data) => {
                this.badgeData = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getTicketsByEmployee() {
            return await this.asyncGetTicketsByEmployee(this.startTime, this.endTime).then((data) => {
                this.ticketByEmployeeData = data.data
            }).catch(res => {
                console.log(res)
            });
        },
        async getCreationByHour() {
            return await this.asyncGetCreationByHour(this.startTime, this.endTime).then((data) => {
                this.creationByHour = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getContractData() {
            return await this.asyncGetContractData(this.startTime, this.endTime).then((data) => {
                this.contractData = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getClosedTasksByEmployee() {
            return await this.asyncGetClosedTasksByEmployee(this.startTime, this.endTime).then((data) => {
                this.closedTasksByEmployee = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getClosedTasksByAdmin() {
            return await this.asyncGetClosedTasksByAdmin(this.startTime, this.endTime).then((data) => {
                this.closedTasksByAdmin = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getDelayByBadge() {
            return await this.asyncGetDelayByBadge(this.startTime, this.endTime).then((data) => {
                this.delayByBadge = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getDelayByEmployee() {
            return await this.asyncGetDelayByEmployee(this.startTime, this.endTime).then((data) => {
                this.delayByEmployee = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        async getCreatedVsResolved() {
            return await this.asyncGetCreatedVsResolved(this.startTime, this.endTime).then((data) => {
                this.createdVsResolved = data.data;
            }).catch(res => {
                console.log(res)
            });
        }
    }
}
</script>
