<template>
	<div id="params" :class="{enabled:enabled}">
		<div class="sticky">

			<nav class="navbar navbar-default">
				<span class="loader"></span>
				<ul class="nav navbar-nav">
					<li>
						<div class="btn-group">
							<button
								id="runState"
								v-if="runIf"
								:data-run="runState ? 1 : 0"
								@click="toggleRunState"
								class="btn btn-default btn-xs"><i class="fa fa-bolt" aria-hidden="true"></i></button>
							<button
								:disabled="!enabled"
								id="run"
								@click="run()"
								class="btn btn-default btn-xs">{{{ runLabel }}}</button>
						</div>
					</li>
					<param v-for="param in params" @run="run" :param="param"></param>
				</ul>
			</nav>

		</div>
	</div>

</template>

<script>

import Param		from './Param.vue';

export default
{
	name: 'Params',

	props: ['defer', 'method', 'params', 'runIf', 'runState'],

	components:
	{
		Param
	},

	computed:
	{
		enabled ()
		{
			return this.method && this.method.name !== 'index';
		},

		runLabel ()
		{
			let label = 'Run';
			if(this.runIf)
			{
				label = this.runState
					? String(this.runIf).replace(/\w/, char => char.toUpperCase())
					: 'Test';
			}
			return label
		},

	},

	methods:
	{
		run ()
		{
			this.$emit('run')
		},

		toggleRunState ()
		{
			this.$emit('runState')
		}
	}

}


</script>

<style lang="scss">

</style>