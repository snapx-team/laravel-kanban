<template>
  <div
    class="border-gray-300 border px-2 pt-2"
    @click="onClick()"
    :class="clicked ? 'unclick-theme' : 'click-theme'"
  >
    {{ board.title }}
    <div class="flex>">
      <div
        id="container"
        v-on:click.stop
        @click="expandPanel()"
        class="flex-1 w-6 text-center"
      >
        <i
          id="icon"
          :class="[showMore ? 'fa-chevron-down' : 'fa-chevron-right', 'fa']"
        ></i>
      </div>

      <div class="flex-1 relative pt-1">
        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
          <div
            :style="{ width: percentAssigned}"
            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"
          ></div>
        </div>
      </div>
    </div>

    <div class="flex-1" v-if="showMore">
      <table>
        <tbody>
          <tr>
            <td class="w-40 pb-2">Active</td>
            <td>33</td>
          </tr>
          <tr>
            <td class="pb-2">Archived</td>
            <td>15</td>
          </tr>
          <tr>
            <td class="pb-2">Assigned</td>
            <td>20</td>
          </tr>
          <tr>
            <td class="pb-2">Unassigned</td>
            <td>19</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  inject: ["eventHub"],
  components: {},
  data() {
    return {
      showMore: false,
      clicked: false,
    };
  },
  props: {
    board: {
      type: Object,
    },
  },
  computed: {
    percentAssigned: function() {
      return this.board.percent/this.board.total*100 + '%'
    }
  },
  methods: {
    expandPanel() {
      this.showMore = !this.showMore;
    },
    onClick() {
      this.eventHub.$emit("filter-by-board", this.board.title);
      this.clicked = !this.clicked;
    },
  },
};
</script>

<style scoped>
.click-theme {
  background-color: #ffffff;
}
.unclick-theme {
  background-color: #acd0f1;
}
</style>