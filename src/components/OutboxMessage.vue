<!--
  - @copyright Copyright (c) 2022 Richard Steinmetz <richard@steinmetz.cloud>
  -
  - @author Richard Steinmetz <richard@steinmetz.cloud>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<ListItem
		class="list-item-style"
		:class="{ selected }"
		:to="link"
		:title="addresses"
		:details="formatted()">
		<template #icon>
			<div
				class="mail-message-account-color"
				:style="{'background-color': accountColor}" />
			<div class="app-content-list-item-icon">
				<Avatar :display-name="addresses" :email="avatarEmail" />
				<p v-if="selectMode" class="app-content-list-item-select-checkbox">
					<input :id="`select-checkbox-${data.uid}`"
						class="checkbox"
						type="checkbox"
						:checked="selected">
					<label
						:for="`select-checkbox-${data.uid}`"
						@click.exact.prevent="toggleSelected"
						@click.shift.prevent="onSelectMultiple" />
				</p>
			</div>
		</template>
		<template #subtitle>
			<span v-if="data.answered" class="icon-reply" />
			<span v-if="data.hasAttachments === true" class="icon-public icon-attachment" />
			<span v-if="data" class="draft">
				<em>{{ t('mail', 'Draft: ') }}</em>
			</span>
			{{ data.subject }}
		</template>
		<template #actions />
	</ListItem>
</template>

<script>
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Avatar from './Avatar'
import { calculateAccountColor } from '../util/AccountColor'

export default {
	name: 'OutboxMessage',
	components: {
		ListItem,
		Avatar,
	},
	props: {
		message: {
			type: Object,
			required: true,
		},
	},
	accountColor() {
		const account = this.$store.getters.getAccount(this.message.accountId)
		return calculateAccountColor(account?.emailAddress ?? '')
	},
}
</script>

<style lang="scss" scoped>
</style>
