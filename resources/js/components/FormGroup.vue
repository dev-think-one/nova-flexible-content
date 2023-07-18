<template>
  <div
    :id="group.key"
    class="mb-4 w-full"
  >
    <div
      class="border border-gray-200 dark:border-gray-700 rounded-t-lg h-8 leading-normal flex items-center box-content"
      :class="{ ' rounded-b-lg': collapsed }"
    >
      <BlockIconButton
        :icon="(collapsed || disabledExpand)?'plus':'minus'"
        :dusk="(collapsed || disabledExpand)?'expand-group':'collapse-group'"
        dusk=""
        class="border-r"
        :title="(collapsed || disabledExpand)?__('Expand'):__('Collapse')"
        @click.prevent="(collapsed || disabledExpand)?expand():collapse()"
        :disabled="disabledExpand"
        :iconClass="{'opacity-50': disabledExpand}"
      />
      <p class="flex-grow px-4 flex items-center overflow-hidden whitespace-nowrap truncate">
        <BlockIdText class="inline" :number="index + 1" :title="group.title"/>
        <Badge
          v-if="descriptionText"
          class="ml-3 bg-primary-50 dark:bg-primary-500 text-primary-600 dark:text-gray-900 space-x-1 truncate"
        >
          {{ descriptionText }}
        </Badge>
      </p>
      <div v-if="!readonly" class="flex">
        <BlockIconButton
          icon="selector"
          dusk="drag-group"
          class="border-l nova-flexible-content-drag-button"
          :title="__('Drag')"
        />
        <BlockIconButton
          icon="arrow-up"
          dusk="move-up-group"
          class="border-l"
          :title="__('Move up')"
          @click.prevent="moveUp"
        />
        <BlockIconButton
          icon="arrow-down"
          dusk="move-down-group"
          class="border-l"
          :title="__('Move down')"
          @click.prevent="moveDown"
        />
        <BlockIconButton
          icon="trash"
          dusk="delete-group"
          class="border-l"
          :title="__('Delete')"
          @click.prevent="confirmRemovingGroup"
        />
        <DeleteGroupModal
          v-if="displayRemoveConfirmation"
          @confirm="remove"
          @close="displayRemoveConfirmation=false"
          :message="field.confirmRemoveMessage"
          :yes="field.confirmRemoveYes"
          :no="field.confirmRemoveNo"
        />
      </div>
    </div>
    <div
      class="flex-grow border-b border-r border-l border-gray-200 dark:border-gray-700 rounded-b-lg"
      :class="{ 'hidden': collapsed }"
    >
      <component
        v-for="(item, index) in group.fields"
        :key="index"
        :is="'form-' + item.component"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :field="item"
        :errors="errors"
        :mode="mode"
        :show-help-text="item.helpText != null"
        :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
      />
    </div>
  </div>
</template>

<script>
import {find} from 'lodash'
import BehavesAsPanel from 'nova-mixins/BehavesAsPanel';
import {mapProps} from 'laravel-nova';
import DeleteGroupModal from '@/components/Modal/DeleteGroup.vue'
import BlockIconButton from '@/components/Block/IconButton.vue'
import BlockIdText from '@/components/Block/IdText.vue'

export default {
  mixins: [BehavesAsPanel],

  components: {DeleteGroupModal, BlockIconButton, BlockIdText},

  props: {
    errors: {},
    group: {},
    index: {},
    field: {},
    ...mapProps(['mode'])
  },

  emits: ['move-up', 'move-down', 'remove'],

  data() {
    return {
      displayRemoveConfirmation: false,
      readonly: this.group.readonly,
    };
  },

  computed: {
    disabledExpand() {
      return this.group.fields.length <= 0;
    },

    collapsed() {
      return this.group.collapsed || this.disabledExpand;
    },

    descriptionText() {
      if (this.group.configs.tagInfoFrom) {
        const field = find(this.group.fields, {attribute: `${this.group.key}__${this.group.configs.tagInfoFrom}`});
        if (field) {
          if (Array.isArray(field.options)) {
            const text = find(field.options, (option) => (('' + option?.value) === '' + field.value))?.label;
            if (text !== undefined) {
              return text;
            }
          }
          return field?.value;
        }
      }

      return null;
    },
  },

  methods: {
    /**
     * Move this group up
     */
    moveUp() {
      this.$emit('move-up');
    },

    /**
     * Move this group down
     */
    moveDown() {
      this.$emit('move-down');
    },

    /**
     * Remove this group
     */
    remove() {
      this.$emit('remove');
    },

    /**
     * Confirm remove message
     */
    confirmRemovingGroup() {
      if (this.field.confirmRemove) {
        this.displayRemoveConfirmation = true;
      } else {
        this.remove()
      }
    },

    /**
     * Expand fields
     */
    expand() {
      this.group.collapsed = false;
    },

    /**
     * Collapse fields
     */
    collapse() {
      this.group.collapsed = true;
    },
  },
}
</script>
