<script setup>
import { computed } from 'vue';
import BlockIdText from '@/components/Block/IdText.vue';
import BlockIconButton from '@/components/Block/IconButton.vue';

const props = defineProps({
  group: {
    type: Object,
    default: null,
  },
  attribute: {
    type: String,
    default: null,
  },
  index: {

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
});

const disabledExpand = computed(() => props.group.fields.length <= 0);

const collapsed = computed(() => props.group.collapsed || disabledExpand.value);

const expand = () => {
  props.group.collapsed = false;
};

const collapse = () => {
  props.group.collapsed = true;
};

</script>

<template>
  <section class="mb-4 w-full">
    <header
      class="border border-gray-200 dark:border-gray-700 rounded-t-lg h-8 leading-normal flex items-center box-content"
      :class="{ ' rounded-b-lg': collapsed }"
    >
      <BlockIconButton
        :icon="(collapsed || disabledExpand)?'plus':'minus'"
        :dusk="(collapsed || disabledExpand)?'expand-group':'collapse-group'"
        class="border-r"
        :title="(collapsed || disabledExpand)?__('Expand'):__('Collapse')"
        :disabled="disabledExpand"
        :icon-class="{'opacity-50': disabledExpand}"
        @click.prevent="(collapsed || disabledExpand)?expand():collapse()"
      />
      <p class="flex-grow px-4 flex items-center overflow-hidden whitespace-nowrap truncate">
        <BlockIdText
          class="inline"
          :number="index + 1"
          :title="group.title"
        />
      </p>
    </header>
    <main
      class="
        flex-grow rounded-b-lg
        border-b border-r border-l
        border-gray-200 dark:border-gray-700
        divide-y divide-gray-100 dark:divide-gray-700
        px-6
      "
      :class="{ 'hidden': collapsed }"
    >
      <component
        :is="`detail-${item.component}`"
        v-for="(item, index) in group.fields"
        :key="index"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :field="item"
        :validation-errors="null"
      />
    </main>
  </section>
</template>
