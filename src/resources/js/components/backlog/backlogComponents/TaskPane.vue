<template>
  <div class="border-gray-300 border-2">
    <div class="h-10 sticky border">
      <button
        type="button"
        class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
        @click="closeTaskView()"
      >
        <i class="fas fa-times"></i>
        <span class="text-xs font-semibold text-center leading-3 uppercase">
          Esc
        </span>
      </button>
    </div>
    {{ task.title }}
    <table>
      <tbody>
        <tr>
          <td class="w-32">Creator</td>
          <td>{{ task.creator }}</td>
        </tr>
        <tr>
          <td>Assigned To</td>
          <td class="flex">
            <template v-for="(user, userIndex) in task.assignedTo">
              <template v-if="userIndex < 3">
                <avatar
                  :key="userIndex"
                  :class="{ '-ml-2': userIndex > 0 }"
                  :name="user.name"
                  :size="6"
                  :borderSize="0"
                  :borderColor="'white'"
                  :tooltip="true"
                ></avatar>
              </template>
            </template>

            <div v-if="task.assignedTo" class="flex">
              <span
                v-if="task.assignedTo.length > 3"
                class="z-10 flex items-center justify-center font-semibold text-gray-800 text-xs w-6 h-6 rounded-full bg-gray-300 border-white border -ml-2 pr-1"
                >+{{ task.assignedTo.length - 3 }}
              </span>
            </div>
          </td>
        </tr>
        <tr>
          <td>Priority</td>
          <td>{{ task.priority.title }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import Avatar from "../../global/Avatar.vue";

export default {
  inject: ["eventHub"],
  components: {
    Avatar,
  },
  props: {
    task: {
      badge: {
        color: {
          type: String,
          default: "gray",
        },
      },
      priority: {
        color: {
          type: String,
          default: "gray",
        },
      },
      creator: Object,
      assignedTo: Object,
      required: true,
      default: () => ({}),
    },
  },
  methods: {
    closeTaskView() {
      this.eventHub.$emit("close-task-view");
    },
  },
};
</script>