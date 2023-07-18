<script setup>
import {ref, computed, nextTick} from "vue";

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

const dropdownElement = ref(null);
const isLayoutsDropdownOpen = ref(false);
const dropdownOrientation = ref('bottom');

const filteredLayouts = computed(() => {
  return props.layouts.filter(layout => {
    const count = props.limitPerLayoutCounter[layout.name];

    return count === null || count > 0;
  });
});

const dropdownClasses = computed(() => ({
  'pin-b mt-3': dropdownOrientation.value === 'bottom',
  'pin-t mb-3': dropdownOrientation.value === 'top',
}));

const addGroup = (layout) => {
  if (!layout) {
    return;
  }

  emit('addGroup', layout);

  isLayoutsDropdownOpen.value = false;
  dropdownOrientation.value = 'bottom';
}

const toggleLayoutsDropdownOrAddDefault = () => {
  if (filteredLayouts.value.length === 1) {
    return addGroup(filteredLayouts.value[0]);
  }

  isLayoutsDropdownOpen.value = !isLayoutsDropdownOpen.value;

  nextTick(() => {
    if (isLayoutsDropdownOpen.value) {
      const {bottom: dropdownBottom} = dropdownElement.value.getBoundingClientRect();

      // If the dropdown is popping out of the bottom of the window, pin it to the top of the button.
      if (dropdownBottom > window.innerHeight) {
        dropdownOrientation.value = 'top';
        return;
      }
    }

    // Reset the orientation.
    dropdownOrientation.value = 'bottom';
  })
}

</script>

<template>
  <div class="relative" v-if="filteredLayouts">
    <div v-if="isLayoutsDropdownOpen && filteredLayouts.length > 1"
         ref="dropdownElement"
         class="
         border border-gray-100 dark:border-gray-700
         rounded-lg shadow-lg max-w-xs
         z-20 absolute overflow-y-auto"
         :class="dropdownClasses"
    >
      <ul class="list-reset">
        <li v-for="layout in filteredLayouts"
            :key="'add-'+layout.name"
            class="border-b border-gray-100 dark:border-gray-700"
        >
          <a
            :dusk="`add-${layout.name}`"
            @click="addGroup(layout)"
            class="cursor-pointer flex items-center py-2 px-3 no-underline font-normal
              bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900
            ">
            {{ layout.title }}
          </a>
        </li>
      </ul>
    </div>
    <DefaultButton
      dusk="toggle-layouts-dropdown-or-add-default"
      type="button"
      tabindex="0"
      @click="toggleLayoutsDropdownOrAddDefault"
    >
      <span>{{ field.button || __('Add layout') }}</span>
    </DefaultButton>
  </div>
</template>

<style scoped>
.pin-b {
  top: 100%;
  bottom: auto;
}

.pin-t {
  top: auto;
  bottom: 100%;
}
</style>


