<?php

class DevelopmentPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Development'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {

startBox("<h1>Stendhal, Arianne, and You</h1>"); ?>
<img src = "/images/ariannelogo.png"  style="float:right;" alt="Arianne Logo"  />
Stendhal is an open source project, written and released under the GNU GPL license by the <a href="https://arianne-project.org">Arianne project</a>. We aim to make it easy to get involved and there are many ways to contribute. If you have an idea for a feature or something you would like different, you're very encouraged to make it happen!
<p>
You can contribute if you are a beginner or experienced Java coder, or just want to learn, artist, map maker, musician, writer, designer, and if you're not sure where you would fit in, you probably do, just take a look around.
<p>
There are loads of tutorials on our wiki including how to set up the Eclipse development environment and get started with your first Java code for a quest, so it's an ideal project for beginners to programming, supported by a friendly development team.
<?php endBox(); ?>
<?php startBox("<h1>Development</h1>"); ?>
<ul id="dmenu" >
	<li><a href="/wiki/StendhalCodeDesign"><img src="/images/buttons/c_code_button.png" alt="">Code</a> - details of code designs and conventions</li>
	<li><a href="/wiki/StendhalRefactoringGraphics"><img src="/images/buttons/c_gfx_button.png" alt="">Graphics</a> - artists, this one is for you</li>
	<li><a href="/wiki/Stendhal_Sound"><img src="/images/buttons/c_snd_button.png" alt="">Sounds &amp; Music</a> </li>
	<li><a href="/wiki/Stendhal_Quest_Contribution"><img src="/images/buttons/quests_button.png" alt="">Quests</a> - planned or ideas</li>
	<li><a href="/wiki/HowToAddItemsStendhal"><img src="/images/buttons/items_button.png" alt="">Items</a></li>
	<li><a href="/wiki/StendhalRefactoringAtlas"><img src="/images/buttons/atlas_button.png" alt="">Maps</a> - for map areas which need improvement</li>
	<li><a href="/wiki/HowToAddCreaturesStendhal"><img src="/images/buttons/creatures_button.png" alt="">Creatures</a> - mostly for your information</li>
</ul>
If you plan to help with Stendhal development it is a very good idea to talk about it with the developers and contributors in the <a href="<?php echo rewriteURL('/development/chat.html');?>">chat channels</a>.
<?php endBox(); ?>
<?php startBox("<h1>Video</h1>"); ?>
<iframe width="560" height="315" src="https://www.youtube.com/embed/x4OpqCWAYaU" name="videoframe" frameborder="0" allowfullscreen></iframe>
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=92" target="videoframe">0:01</a> Introduction
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=92" target="videoframe">1:32</a> Source code download
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=131" target="videoframe">2:11</a> Development portal from stendhalgame.org/development
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=169" target="videoframe">2:49</a> Stendhal Quest Contribution
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=307" target="videoframe">5:07</a> Graphics, Sound, Items, Maps, Creatures
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=328" target="videoframe">5:28</a> Chatting to developers in IRC
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=440" target="videoframe">7:20</a> Java Code navigation and design
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=467" target="videoframe">7:47</a> Bugs and features tracker
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=590" target="videoframe">9:50</a> Patch contribution
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=600" target="videoframe">10:00</a> Eclipse as an IDE
<br><a href="https://www.youtube.com/embed/x4OpqCWAYaU?autoplay=1&start=620" target="videoframe">10:20</a> Conclusion
<?php endBox();
	}
}
$page = new DevelopmentPage();
