<script setup>

const props = defineProps({
  title: {
    type: String,
    default: null,
  },
  number: {
    type: [String, Number],
    default: 8,
  },
});

</script>

<template>
  <div>
    <span
      class="font-bold"
      :class="{'mr-2': !!title}"
    >
      #{{ number }}
    </span>
    {{ title }}
  </div>
</template>
