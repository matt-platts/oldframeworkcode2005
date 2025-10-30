<?php

$description=<<<EOF

<p style="text-align: center;"><span style="font-size: large;">A FANTASTIC 4DVD SET WITH 58 PAGE BOOKLET!</span></p>
<p style="text-align: center;"><span style="font-size: medium;"><span style="font-size: medium;">Includes Britten's famous but long out-of-print </span></span></p>
<p style="text-align: center;"><span style="font-size: medium;"><span style="font-size: medium;">Aspen Award Speech.</span></span></p>
<p style="text-align: justify;">"<em>What matters to us now is that people want to use our music. For that, as I see it, is our job. To be useful, and to the living.</em>" - <strong>BENJAMIN BRITTEN</strong></p>
<p style="text-align: justify;">&ldquo;<strong>Ben Britten was a man at odds with the world.</strong>&rdquo; So begins Leonard Bernstein in my film <strong>A Time There Was</strong>, one of <span style="text-decoration: underline;">four films included in the new boxset</span>, <strong>BRITTEN AT 100</strong> &ndash; the others being my 1967 film <strong>Benjamin Britten and his Festival</strong>; the film of the recording in Orford Church of Britten&rsquo;s opera, <strong>The Burning Fiery Furnace</strong>; and my 1980 film of his opera <strong>Death in Venice</strong>, filmed in Venice with all the original cast.</p>
<p style="text-align: justify;">The boxset also contains Britten&rsquo;s famous, but long out-of-print, <strong>1964 Aspen Award speech,</strong> illustrated with over <strong>50 photos</strong>, some never published before.</p>
<p style="text-align: justify;">&ldquo;It&rsquo;s strange,&rdquo; Bernstein went on. &ldquo;On the surface Britten&rsquo;s music would seem to be decorative, positive, charming, but it&rsquo;s so much more than that. If you really listen to (his music), you become aware of something very dark. There are gears that are grinding and not quite meshing, and they make a great pain.(For him) it was a difficult and lonely time. Yes, he was a man at odds with the world&hellip; and he didn&rsquo;t show it.&rdquo;</p>
<p style="text-align: justify;">I can think of no better summary of this extraordinary man, Britten, who was born in the same town where I went to school and whom I was privileged to know. Not long ago I sat next to a famous conductor who told me confidently that before long, because it was so &ldquo;fatally flawed&rdquo; (the obsession with children and children&rsquo;s voices, for instance), Britten&rsquo;s music would soon be forgotten.</p>
<p style="text-align: justify;">I hope he lives long enough to eat his words since I have no doubt that Britten&rsquo;s music will be cherished long after this particular conductor, like many of his ilk, will have been totally forgotten. And this preoccupation with Britten&rsquo;s &lsquo;children&rsquo;, really little more than a footnote, trivialises his greatness both as a composer and as a man.</p>
<p style="text-align: justify;">As a man he could be vindictive, petty, childish, selfish and cruel. I know; I was on the sharp end of his tongue more than once, and there are legions of wonderful musicians who found themselves banished from his presence, often for no reason they were able to fathom.</p>
<p style="text-align: justify;">And then you ask yourself: how could such a man write the <strong>War Requiem</strong>, or that supreme masterpiece among his song cycles, <strong>The Nocturne</strong>, or the so-called &lsquo;Hyde Park aria&rsquo; from <strong>Owen Wingrave</strong>?</p>
<p style="text-align: justify;">Or, as Humphrey Carpenter once said - after a very long pause &ndash; following the broadcast on Radio 3 of a recording of the <strong>Young Person&rsquo;s Guide to the Orchestra</strong>, conducted by Britten himself: &ldquo;that, ladies and gentlemen, is pure genius.&rdquo;</p>

EOF;

$orig=$description;
$description=strip_tags($description);
$description=substr($description,0,300);
$str = $orig . "\n\n\n\n\n\n\n" . $description;
print $description;


?>
