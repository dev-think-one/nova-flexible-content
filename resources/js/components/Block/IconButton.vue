<script setup>
import { computed } from 'vue';
import { round } from 'lodash';

const props = defineProps({
  icon: {
    type: String,
    required: true,
  },
  size: {
    type: [String, Number],
    default: 8,
  },
  title: {
    type: String,
    default: null,
  },
  iconClass: {
    type: [Array, Object, String],
    default: null,
  },
});

const iconSize = computed(() => round(props.size / 2, 0));
</script>

<template>
  <button
    type="button"
    class="border-gray-200 dark:border-gray-700 block"
    :class="[`w-${size}`, `h-${size}`]"
    :title="title"
  >
    <Icon
      :type="icon"
      class="align-top"
      :class="[iconClass, `w-${iconSize}`, `h-${iconSize}`]"
    />
  </button>
</template>
