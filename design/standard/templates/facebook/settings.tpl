{ezcss_require( array( 'nxc_social_networks.css' ) )}

{if $connected}
<div class="message-feedback">
	<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span> {'Facebook account connected.'|i18n( 'extension/nxc_social_networks' )}</h2>
</div>
{/if}

<div class="context-block">

	<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
		<h1 class="context-title">{'Connect Facebook account:'|i18n( 'extension/nxc_social_networks' )}</h1>
		<div class="header-subline"></div>
	</div></div></div></div></div></div>

	<div class="box-ml"><div class="box-mr"><div class="box-content">

		<div class="context-toolbar">
			<div class="block"></div>
		</div>

		<div class="content-navigation-childlist">
			<div class="nxc-facebook-icon-container">
				<a id="nxc-facebook-connect" href="{'nxc_facebook_api/authorize_redirect'|ezurl( 'no' )}">
					<img alt="{'Connect Facebook account:'|i18n( 'extension/nxc_social_networks' )}" src="{'facebook/connect.png'|ezimage( 'no' )}" />
				</a>
			</div>
		</div>

		<div class="context-toolbar">
			<div class="block"></div>
		</div>

	</div></div></div>

	<div class="controlbar">
		<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
			<div class="block"></div>
		</div></div></div></div></div></div>
	</div>

</div>