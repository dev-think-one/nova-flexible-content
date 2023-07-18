<script setup>
import { useCopyValueToClipboard } from 'nova-mixins/CopiesToClipboard';
import { useLocalization } from 'laravel-nova'
import BlockIconButton from '@/components/Block/IconButton.vue';

const props = defineProps({
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
});

const { __ } = useLocalization()
const { copyValueToClipboard } = useCopyValueToClipboard();

const copyToClipboard = () => {
  copyValueToClipboard(JSON.stringify(props.field.value));

  Nova.success(__('Filed value copied to clipboard.'));
};

</script>

<template>
  <div>
    <BlockIconButton
      icon="document-duplicate"
      class="border rounded-lg"
      :title="__('Copy to clipboard')"
      @click.stop="copyToClipboard"
    />
  </div>
</template>
