<template>
  <Modal :show="true">
    <form
      class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
      @submit.prevent="$emit('confirm')"
    >
      <slot>
        <ModalHeader v-text="__('Delete Group')" />
        <ModalContent>
          <p
            v-if="message"
            class="leading-normal"
          >
            {{ message }}
          </p>
          <p
            v-else
            class="leading-normal"
          >
            {{ __('Are you sure you want to delete this group?') }}
          </p>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <link-button
            type="button"
            data-testid="cancel-button"
            dusk="cancel-delete-button"
            class="mr-3"
            @click.prevent="$emit('close')"
          >
            {{ no }}
          </link-button>

          <danger-button
            ref="confirmButton"
            dusk="confirm-delete-button"
            :processing="working"
            :disabled="working"
            type="submit"
          >
            {{ yes }}
          </danger-button>
        </div>
      </ModalFooter>
    </form>
  </Modal>
</template>

<script>
export default {
  props: ['message', 'yes', 'no'],
  emits: ['close', 'confirm'],
};
</script>
