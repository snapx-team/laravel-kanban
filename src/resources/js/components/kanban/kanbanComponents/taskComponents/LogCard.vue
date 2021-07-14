<template>

    <tr>
        <td>{{ moment(log.created_at).format("MMMM Do YYYY") }}</td>
        <td>{{ log.log_type }}</td>
        <td>{{ log.description }}</td>
        <td>
            <div v-if="log.user && log.user.full_name">
                {{ log.user.full_name }}
            </div>
        </td>
        <td>{{ log.role }}</td>
        <td>
            <div v-if="log.badge && log.badge.name">
                {{ log.badge.name }}
            </div></td>
        <td>
            <div v-if="log.board && log.board.name">
                {{ log.board.name }}
            </div>
        </td>
        <td>
             <div v-if="log.erp_job_site && log.erp_job_site.name">
                {{ log.erp_job_site.name }}
            </div>
        </td>
        <td>
            <div v-if="log.erp_employee && log.erp_employee.full_name">
                {{ log.erp_employee.full_name }}
            </div>
        </td>
    </tr>
  
</template>

<script>
import moment from 'moment'

export default {
  inject: ["eventHub"],
  components: {},
  created: function () {
    this.moment = moment;
  },
  props: {
    log: {},
  },
  methods: {
  },
};
</script>