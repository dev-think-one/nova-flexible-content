<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  layouts: {
    type: Array,
    default: () => [],
  },
  field: {
    type: Object,
    default: null,
  },
  resourceName: {
    type: String,
    default: null,
  },
  resourceId: {
    type: [Number, String],
    default: null,
  },
  errors: {
    type: Array,
    default: null,
  },
  limitCounter: {
    type: Object,
    default: () => ({}),
  },
  limitPerLayoutCounter: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['addGroup']);

const searchText = ref(null);

const options = computed(() => {
  if (!props.layouts || !Array.isArray(props.layouts)) {
    return [];
  }

  const filteredLayouts = props.layouts.filter((layout) => {
    if (searchText.value && searchText.value.length > 0) {
      if (!layout.title.toLowerCase().includes(searchText.value.toLowerCase())) {
        return false;
      }
    }

    const count = props.limitPerLayoutCounter[layout.name];

    return count === null || count > 0;
  });

  return filteredLayouts.map((layout) => ({
    value: layout.name,
    display: layout.title,
  }));
});

const selectLayout = (selectedOption) => {
  const layout = props.layouts.find((layout) => layout.name === selectedOption.value);
  if (layout) {
    emit('addGroup', layout);
  }
};

</script>

<template>
  <SearchInput
    v-if="options.length > 0"
    :dusk="`${field.attribute}--search-input`"
    :data="options"
    :clearable="false"
    track-by="value"
    class="w-60"
    :mode="mode"
    @input="searchText = $event"
    @selected="selectLayout"
  >
    <span>{{ field.button || __('Add layout') }}</span>

    <template #option="{ selected, option }">
      <SearchInputResult
        :option="option"
        :selected="selected"
        :with-subtitles="false"
      />
    </template>
  </SearchInput>
</template>
