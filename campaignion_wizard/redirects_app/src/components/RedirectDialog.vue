<template lang="html">
  <ElDialog
    :title="dialogTitle"
    :visible="visible"
    :close-on-click-modal="false"
    size="large"
    :before-close="dialogCancelHandler"
    >

    <section class="pra-redirect-label">
      <label :for="'pra-redirect-label-' + _uid">{{ text('Redirect label') }} <small>{{ text('seen only by you') }}</small></label>
      <input type="text" v-model="currentRedirect.label" class="field-input" :id="'pra-redirect-label-' + _uid">
    </section>

    <FilterEditor
      :fields="$root.$options.settings.fields"
      :filters.sync="currentRedirect.filters"
      :operators="OPERATORS"
    />

    <section class="pra-redirect-destination">
      <label :for="'pra-redirect-destination-' + _uid">{{ text('Redirect destination') }} <small>{{ text('type a node title or ID or paste a URL') }}</small></label>
      <DestinationField
      :id="'pra-redirect-destination-' + _uid"
      :class="{'pra-has-error': showErrors && !destinationIsValid}"
      :value="destination"
      :placeholder="text('Type to search nodes')"
      :show-dropdown-on-focus="true"
      data-key="values"
      label-key="label"
      :url="$root.$options.settings.endpoints.nodes"
      :headers="{}"
      search-param="s"
      :count="20"
      @input="item => {destination = item}"
      />
      <div v-if="showErrors && !destinationIsValid" class="pra-error-message">{{ text('destination error') }}</div>
    </section>

    <span slot="footer" :class="{'pra-dialog-footer': true, 'pra-dialog-alert': modalDirty}">
      <span v-if="modalDirty" class="pra-dialog-alert-message">{{ text('unsaved changes') }}</span>
      <el-button @click="cancelButtonHandler()" class="js-modal-cancel">{{ text('Cancel') }}</el-button>
      <el-button type="primary" :disabled="currentRedirectIsEmpty" @click="updateRedirect" class="js-modal-save">{{ text('Done') }}</el-button>
    </span>

  </ElDialog>
</template>

<script>
import {clone, validateDestination} from '@/utils'
import {OPERATORS, emptyRedirect} from '@/utils/defaults'
import {mapState} from 'vuex'
import {isEqual, omit} from 'lodash'
import DestinationField from './DestinationField'
import FilterEditor from './FilterEditor'

export default {
  components: {
    DestinationField,
    FilterEditor
  },

  data () {
    return {
      currentRedirect: emptyRedirect(),
      modalDirty: false,
      showErrors: false,
      OPERATORS
    }
  },

  computed: {
    dialogTitle () {
      if (this.currentRedirectIndex === -1) {
        return Drupal.t('Add personalized redirect')
      } else if (this.currentRedirectIndex >= 0) {
        if (this.currentRedirect.label) {
          return Drupal.t('Edit @itemName', {'@itemName': this.currentRedirect.label})
        } else {
          return Drupal.t('Edit personalized redirect')
        }
      }
    },
    currentRedirectIsEmpty () {
      return this.currentRedirectIndex !== null && isEqual(omit(this.currentRedirect, ['id', 'prettyDestination']), omit(emptyRedirect(), ['id', 'prettyDestination']))
    },
    visible () {
      return this.currentRedirectIndex !== null
    },
    destination: {
      // destination and prettyDestination translated for the DestinationField
      // component as value and label
      get () {
        return {
          value: this.currentRedirect.destination,
          label: this.currentRedirect.prettyDestination
        }
      },
      set (val) {
        this.currentRedirect.destination = val.value
        this.currentRedirect.prettyDestination = val.label
      }
    },
    destinationIsValid () {
      return validateDestination(this.currentRedirect.destination)
    },
    ...mapState([
      'redirects',
      'currentRedirectIndex'
    ])
  },

  methods: {
    text (text) {
      switch (text) {
        case 'Redirect label': return Drupal.t('Redirect label')
        case 'seen only by you': return Drupal.t('(seen only by you)')
        case 'Redirect destination': return Drupal.t('Redirect destination')
        case 'type a node title or ID or paste a URL': return Drupal.t('type a node title or ID or paste a URL')
        case 'Type to search nodes': return Drupal.t('Type to search nodes')
        case 'destination error': return Drupal.t('Please enter a valid URL or choose a node.')
        case 'unsaved changes': return Drupal.t('You have unsaved changes!')
        case 'Cancel': return this.modalDirty ? Drupal.t('Discard my changes') : Drupal.t('Cancel')
        case 'Done': return Drupal.t('Done')
      }
    },
    tryClose (options) {
      // Any changes?
      if (this.currentRedirectIndex !== -1 && isEqual(this.currentRedirect, this.redirects[this.currentRedirectIndex]) ||
        this.currentRedirectIndex === -1 && this.currentRedirectIsEmpty ||
        (this.modalDirty && options && options.button === 'cancel')) {
        // No changes or force close via cancel button: allow to close modal.
        return true
      } else {
        // There are unsaved changes, alert!
        this.modalDirty = true
        return false
      }
    },
    dialogCancelHandler (done) {
      if (this.tryClose()) {
        this.close()
        done()
      }
    },
    cancelButtonHandler () {
      if (this.tryClose({button: 'cancel'})) {
        this.close()
      }
    },
    updateRedirect () {
      if (!this.destinationIsValid) {
        this.showErrors = true
        return
      }
      this.$store.commit({type: 'updateRedirect', redirect: this.currentRedirect})
      this.close()
    },
    close () {
      this.modalDirty = false
      this.showErrors = false
      this.$store.commit('leaveRedirect')
      this.$root.$emit('closeRedirectDialog')
    }
  },

  mounted () {
    this.$root.$on('newRedirect', () => {
      this.currentRedirect = emptyRedirect()
      this.$store.commit('editNewRedirect')
    })
    this.$root.$on('editRedirect', index => {
      this.currentRedirect = clone(this.redirects[index])
      this.$store.commit({type: 'editRedirect', index})
    })
    this.$root.$on('duplicateRedirect', index => {
      const duplicate = clone(this.redirects[index])
      duplicate.id = emptyRedirect().id
      duplicate.label = Drupal.t('Copy of @redirectLabel', {'@redirectLabel': duplicate.label})
      this.currentRedirect = duplicate
      this.$store.commit('editNewRedirect')
    })
    document.addEventListener('keyup', e => {
      // Catch Enter key and save redirect.
      if (this.visible && !this.currentRedirectIsEmpty && e.keyCode === 13 && document.activeElement.tagName.toLowerCase() !== 'textarea') {
        e.preventDefault()
        this.updateRedirect()
      }
    })
  }
}
</script>

<style lang="css">
</style>
