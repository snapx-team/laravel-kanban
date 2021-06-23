<template>
  <div
    class="px-3 py-1 rounded text-xs font-semibold flex items-center"
    :class="`bg-${color}-100 text-${color}-700`"
  >
    <span class="w-2 h-2 rounded-full mr-1" :class="`bg-${color}-400`"></span>
    <span>
      <slot></slot>
    </span>
  </div>
</template>
<script>
export default {
  props: {
    color: {
      type: String,
      default: "blue",
    },
  },
};
</script>
