<script setup>

const props = defineProps({
  message: {
    type: String,
    default: null,
  },
  yes: {
    type: String,
    default: null,
  },
  no: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['close', 'confirm']);

</script>

<template>
  <Modal :show="true">
    <form
      class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
      @submit.prevent="emit('confirm')"
    >
      <slot>
        <ModalHeader v-text="__('Delete Group')" />
        <ModalContent>
          <p class="leading-normal">
            {{ message || __('Are you sure you want to delete this group?') }}
          </p>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <CancelButton
            type="button"
            dusk="cancel-delete-button"
            class="mr-3"
            @click.prevent="emit('close')"
          >
            {{ no || __('Cancel') }}
          </CancelButton>

          <DangerButton
            dusk="confirm-delete-button"
            type="submit"
          >
            {{ yes || __('Delete') }}
          </DangerButton>
        </div>
      </ModalFooter>
    </form>
  </Modal>
</template>
