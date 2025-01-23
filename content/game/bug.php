<?php

class BugPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Bug Reporting'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
startBox("<h1>Bugs</h1>"); ?>
Reporting bugs is very important so that we can keep Stendhal running smoothly. So, if a bug is worth asking /support or a developer about, it is worth submitting a bug report on.

<p>We have made this page to help you with the process of submitting a bug report, because we find them so helpful.

<p>You can submit a new report in our Codeberg project: <a href="https://codeberg.org/arianne/stendhal/issues/new/choose">Open bug report</a>

<p>Or browse the <a href="https://codeberg.org/arianne/stendhal/issues">issue tracker</a>.
Or the old trackers at <a href="https://github.com/arianne/stendhal/issues">GitHub</a>
or <a href="https://sourceforge.net/p/arianne/bugs/">SourceForge</a>.</p>

<p>We are more than happy to close reports that are not really bugs, and would prefer you to submit a bug report and us close it, than not submit it and we never find out about it. Having said that, before submitting a bug you should scan over the previously posted bugs summaries so that you don't report an already known bug.

<?php endBox(); ?>
<?php startBox("<h2>Making a Report</h2>"); ?>

If you need help submitting a bug report feel free to ask the developers
and contributors in the
<?php echo '<a href="'.rewriteURL('/development/chat.html').'">'?>
development chat channels</a>. These channels are logged, so someone
will respond when they are available.

<p>If there are multiple bugs to report, please open an individual
report for each.</p>
<ul>
<li>Title - This is a short summary of the bug report. Choose a
meaningful and brief sentence.
	<ul>
	<li>Bad title: "there is a bug", "bug found" "error occurred"</li>
	<li>Good title: "Stendhal 0.67 map wrong at x y in -3_semos_cave" or "Test client: buddies panel does not show buddies online state"</li>
	</ul>
<li>In the detailed description please include if possible:</li>
	<ul>
	<li>Where were you when the bug happened? (use '/where yourUserName' in game)</li>
	<li>What did you do when the bug happened?</li>
	<li>What did happen, what did you expect to happen</li>
	<li>Is it reproducable? If so: Which steps are needed to reproduce it?</li>
	<li>When talking about map-errors like 'you can walk under a chair' please provide the exact position and if possible a little screenshot.</li>
	<li>If things in client look weird a screenshot says more than 1000 words</li>
	<li>Your email address, if you are not a logged in member (SourceForge only <small>unless you have a good reason to think it's irrelevant)</small></li>
	<li>Your Operating system</li>
	<li>Whether you are using the web client or downloaded the Java client</li>
	<li>What Java version you have if you are using the Java client (In Linux, type <i>java --version</i> in a command line. Windows users check <a href="https://www.java.com/en/download/help/version_manual.html" class="external text" rel="nofollow">here</a>)</li>
	<li>Any error logs</li>
		<ul>
		<li><span style="color:darkgreen;">$HOME/.config/stendhal/log/stendhal.txt</span> on Linux</li>
		<li><span style="color:darkgreen;">%USERPROFILE%\stendhal\log\stendhal.txt</span> on Windows</li>
		</ul>
	</ul>
<li>GitHub optional fields:</li>
	<ul>
	<li>Labels - predefined keywords used to describe what the report is about</li>
		<ul>
		<li>Example: "component: javaclient" means that the issue is directly related to the Java client</li>
		</ul>
	<li>Files can be attached by clicking the line below the description area or by dragging and dropping files directly into the description</li>
	<li>Everything Else - igore, the remaining fields will be filled out by an administrator</li>
	</ul>
<li>SourceForge optional fields:</li>
	<ul>
	<li>Labels - keywords that can be used to identify this report in searches</li>
	<li>Mark as Private - tick the check box for sensitive or easily abused issues</li>
	<li>Attachments - here is where to attach screenshots or text output of error logs</li>
	<li>Everything Else - igore, the remaining fields will be filled out by an administrator</li>
	</ul>
</ul>
<?php endBox(); ?>
<?php startBox("<h2>Pre release Testers</h2>"); ?>
<p>If you are connecting to the test server using the Java client,
please be sure your client is up-to-date with the most recent build.
</p>
<p>Please include in the bug report whether the issue occurred on the
main or test server. Also include which client (e.g. web client, release
Java client, or testing Java client) was being used if it is relevant.
<?php endBox(); ?>
<?php startBox("<h2>Developers</h2>"); ?>
<p>Please confirm that the bug is still relevant by testing if it occurs
in build from latest Git master branch.</p>
<p>Remember the importance of 'ant clean' or whatever the equivalent is
in your IDE.</p>
<?php endBox();
	}
}
$page = new BugPage();
