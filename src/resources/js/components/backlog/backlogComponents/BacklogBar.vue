<template>
  <div class="flex flex-wrap p-4 pl-10">
    <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">Backlog</h3>
  </div>
</template>

<script>
export default {
  inject: ["eventHub"],
  components: {},
  props: {
    board: {
      type: Array,
      default: () => [],
    },
  },
  methods: {},
};
</script>

<style scoped>
.rotate-45 {
  --transform-rotate: 45deg;
  transform: rotate(45deg);
}

.group:hover .group-hover\:flex {
  display: flex;
}
</style>