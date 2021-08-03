<template>
    <div>
        <backlog-bar :name="'Metrics'"></backlog-bar>
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>
        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <div class="flex-1 space-y-2">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Start</span>
                <date-picker type="datetime" v-model="start"
                             placeholder="YYYY-MM-DD"
                             format="YYYY-MM-DD"
                ></date-picker>
                <div class="flex-1 space-y-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">End</span>
                    <date-picker type="datetime" v-model="end"
                                 placeholder="YYYY-MM-DD"
                                 format="YYYY-MM-DD"
                    ></date-picker>
                </div>
            </div>

            <button @click="filter()"
                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">

                <span>Filter</span>
            </button>

            <div class="flex">
                <div class="mr-2">
                    <div class="table border w-80">
                        <div class="table-row">
                            <div class="table-cell border cursor-pointer text-lg" @click="showTicketBreakdown()">
                                <div class="m-2">
                                    Ticket Breakdown
                                </div>
                            </div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell border cursor-pointer text-lg" @click="showTicketEmployee()">
                                <div class="m-2">
                                    Ticket Employee Breakdown
                                </div>
                            </div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell border cursor-pointer text-lg" @click="showTicketStats()">
                                <div class="m-2">
                                    Ticket Statistics
                                </div>
                            </div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell border cursor-pointer text-lg" @click="showHourlyStats()">
                                <div class="m-2">
                                    Hourly Statistics
                                </div>
                            </div>
                        </div>
                        <div class="table-row">
                            <div class="table-cell border cursor-pointer text-lg" @click="showCreatedVsResolved()">
                                <div class="m-2">
                                    Created Vs Resolved
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fex flex-1 bg-gray-100 py-3">
                    <div class="p-2 flex-grow">
                        <div v-if="showTB" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <pie-chart v-if="badgeData != null" :series="badgeData.hits" :labels="badgeData.names"
                                       :title="'Tickets by Badge'"></pie-chart>
                        </div>
                        <div v-if="showTE" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <pie-chart v-if="ticketByEmployeeData != null" :series="ticketByEmployeeData.hits"
                                       :labels="ticketByEmployeeData.names"
                                       :title="'Tickets Created by Employee'"></pie-chart>
                        </div>
                        <div v-if="showTB" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <pie-chart v-if="jobsiteData != null" :series="jobsiteData.hits" :labels="jobsiteData.names"
                                       :title="'Tickets by JobSite'"></pie-chart>
                        </div>
                        <div v-if="showTE" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <pie-chart v-if="closedTasksByEmployee != null" :series="closedTasksByEmployee.hits"
                                       :labels="closedTasksByEmployee.names"
                                       :title="'Tickets Closed by Employee'"></pie-chart>
                        </div>
                        <div v-if="showTS" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <bar-chart v-if="creationByHour != null" :data="creationByHour.hits"
                                       :categories="creationByHour.names" :xname="'Tickets'" :yname="'Hour'"
                                       :title="'Tickets by Hour Created'"></bar-chart>
                        </div>
                        <div v-if="showHS" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <bar-chart v-if="delayByBadge != null" :data="delayByBadge.hits"
                                       :categories="delayByBadge.names" :xname="'Hours'" :yname="'Categories'"
                                       :title="'Average Hours by Badge'"></bar-chart>
                        </div>
                        <div v-if="showHS" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <bar-chart v-if="delayByEmployee != null" :data="delayByEmployee.hits"
                                       :categories="delayByEmployee.names" :xname="'Hours'" :yname="'Employees'"
                                       :title="'Average Hours by Employee'"></bar-chart>
                        </div>
                        <div v-if="showCR" class="flex-auto rounded shadow-lg m-2 bg-white">
                            <line-chart v-if="createdVsResolved != null" :series="createdVsResolved.series"
                                        :categories="createdVsResolved.categories" :xname="'Days'"
                                        :yname="'Total Tickets'" :title="'Created Vs Resolved'"></line-chart>
                            <!-- :data="delayByEmployee.hits"   -->
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
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue"

export default {
    inject: ["eventHub"],
    components: {
        PieChart,
        BarChart,
        LineChart,
        BacklogBar
    },
    data() {
        return {
            badgeData: null,
            ticketByEmployeeData: null,
            creationByHour: null,
            jobsiteData: null,
            closedTasksByEmployee: null,
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
                this.getBadgeData();
                this.getJobSiteData();
            }
            if (this.showTE) {
                this.getTicketsByEmployee();
                this.getClosedTasksByEmployee();
            }
            if (this.showTS) {
                this.getCreationByHour();
            }
            if (this.showHS) {
                this.getDelayByBadge();
                this.getDelayByEmployee();
            }
            if (this.showCR) {
                this.getCreatedVsResolved();
            }

        },
        showTicketBreakdown() {
            this.showTB = true;
            this.showTE = false;
            this.showTS = false;
            this.showHS = false;
            this.showCR = false;
            this.getBadgeData();
            this.getJobSiteData();
        },
        showTicketEmployee() {
            this.showTB = false;
            this.showTE = true;
            this.showTS = false;
            this.showHS = false;
            this.showCR = false;
            this.getTicketsByEmployee();
            this.getClosedTasksByEmployee();
        },
        showTicketStats() {
            this.showTB = false;
            this.showTE = false;
            this.showTS = true;
            this.showHS = false;
            this.showCR = false;
            this.getCreationByHour();
        },
        showHourlyStats() {
            this.showTB = false;
            this.showTE = false;
            this.showTS = false;
            this.showHS = true;
            this.showCR = false;
            this.getDelayByBadge();
            this.getDelayByEmployee();
        },
        showCreatedVsResolved() {
            this.showTB = false;
            this.showTE = false;
            this.showTS = false;
            this.showHS = false;
            this.showCR = true;
            this.getCreatedVsResolved();
        },
        getMonday(d) {
            d = new Date(d);
            var day = d.getDay(),
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
        getBadgeData() {
            this.asyncGetBadgeData(this.startTime, this.endTime).then((data) => {
                this.badgeData = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        getTicketsByEmployee() {
            this.asyncGetTicketsByEmployee(this.startTime, this.endTime).then((data) => {
                this.ticketByEmployeeData = data.data
            })
        },
        getCreationByHour() {
            this.asyncGetCreationByHour(this.startTime, this.endTime).then((data) => {
                this.creationByHour = data.data;
            })
        },
        getJobSiteData() {
            this.asyncGetJobSiteData(this.startTime, this.endTime).then((data) => {
                this.jobsiteData = data.data;
            })
        },
        getClosedTasksByEmployee() {
            this.asyncGetClosedTasksByEmployee(this.startTime, this.endTime).then((data) => {
                this.closedTasksByEmployee = data.data;
            })
        },
        getDelayByBadge() {
            this.asyncGetDelayByBadge(this.startTime, this.endTime).then((data) => {
                this.delayByBadge = data.data;
            })
        },
        getDelayByEmployee() {
            this.asyncGetDelayByEmployee(this.startTime, this.endTime).then((data) => {
                this.delayByEmployee = data.data;
            })
        },
        getCreatedVsResolved() {
            this.asyncGetCreatedVsResolved(this.startTime, this.endTime).then((data) => {
                this.createdVsResolved = data.data;
            })
        }
    }
}
</script>
