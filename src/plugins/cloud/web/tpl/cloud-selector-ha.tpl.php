<!--
/*
    openQRM Enterprise developed by openQRM Enterprise GmbH.

    All source code and content (c) Copyright 2012, openQRM Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the GNU General Public License version 2, unless otherwise agreed with openQRM Enterprise GmbH.
    The latest version of this license can be found here: src/doc/LICENSE.txt

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://openqrm-enterprise.com

    Copyright 2012, openQRM Enterprise GmbH <info@openqrm-enterprise.com>
*/
-->

<style type="text/css">

a {
	text-decoration:none
}


#add_prod {
	position: relative;
	left: 0px;
	top: 0px;
	width: 480px;
	height: 50px;
}


#quantity_content-slider {
	position: relative;
	left: 10px;
	top: 40px;
	width: 70px;
	height: 6px;
	background: #BBBBBB;
}

.quantity_content-slider-handle {
	background: #478AFF;
	border: solid 3px black;
}



#add_product {
	position: relative;
	left: 5px;
	top: 0px;
	width: 180px;
	height: 6px;
}

#add_price {
	position: relative;
	left: 55px;
	top: 0px;
	width: 320px;
	height: 6px;
}

#ccus {
	position: relative;
	left: 280px;
	top: 0px;
	width: 40px;
	height: 6px;
}


#add_submit {
	position: relative;
	left: 370px;
	top: 0px;
	width: 40px;
	height: 6px;
}


</style>


<h1><img border=0 src="/openqrm/base/img/ha.png"> {cloud_selector_ha} {cloud_selector_products}</h1>

<h4>{cloud_selector_add_product}</h4>
<br>
{cloud_selector_howto_add_product}

<form action={thisfile} method=post>
{form}
<br>
<div id="add_prod">

<div id="add_product">
	<nobr>
	<select title="price" name="product_quantity">
		<option value="1">{cloud_selector_ha}</option>
	</select>

	<strong> <- {cloud_selector_equals} -> </strong>
	<select title="price" name="product_price">
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		<option value="9">9</option>
		<option value="10">10</option>
		<option value="11">11</option>
		<option value="12">12</option>
		<option value="13">13</option>
		<option value="14">14</option>
		<option value="15">15</option>
		<option value="16">16</option>
		<option value="17">17</option>
		<option value="18">18</option>
		<option value="19">19</option>
		<option value="20">20</option>
		<option value="21">21</option>
		<option value="22">22</option>
		<option value="23">23</option>
		<option value="24">24</option>
		<option value="25">25</option>
		<option value="26">26</option>
		<option value="27">27</option>
		<option value="28">28</option>
		<option value="29">29</option>
		<option value="30">30</option>
		<option value="31">31</option>
		<option value="32">32</option>
		<option value="33">33</option>
		<option value="34">34</option>
		<option value="35">35</option>
		<option value="36">36</option>
		<option value="37">37</option>
		<option value="38">38</option>
		<option value="39">39</option>
		<option value="40">40</option>
		<option value="41">41</option>
		<option value="42">42</option>
		<option value="43">43</option>
		<option value="44">44</option>
		<option value="45">45</option>
		<option value="46">46</option>
		<option value="47">47</option>
		<option value="48">48</option>
		<option value="49">49</option>
		<option value="50">50</option>
		<option value="51">51</option>
		<option value="52">52</option>
		<option value="53">53</option>
		<option value="54">54</option>
		<option value="55">55</option>
		<option value="56">56</option>
		<option value="57">57</option>
		<option value="58">58</option>
		<option value="59">59</option>
		<option value="60">60</option>
		<option value="61">61</option>
		<option value="62">62</option>
		<option value="63">63</option>
		<option value="64">64</option>
		<option value="65">65</option>
		<option value="66">66</option>
		<option value="67">67</option>
		<option value="68">68</option>
		<option value="69">69</option>
		<option value="70">70</option>
		<option value="71">71</option>
		<option value="72">72</option>
		<option value="73">73</option>
		<option value="74">74</option>
		<option value="75">75</option>
		<option value="76">76</option>
		<option value="77">77</option>
		<option value="78">78</option>
		<option value="79">79</option>
		<option value="80">80</option>
		<option value="81">81</option>
		<option value="82">82</option>
		<option value="83">83</option>
		<option value="84">84</option>
		<option value="85">85</option>
		<option value="86">86</option>
		<option value="87">87</option>
		<option value="88">88</option>
		<option value="89">89</option>
		<option value="90">90</option>
		<option value="91">91</option>
		<option value="92">92</option>
		<option value="93">93</option>
		<option value="94">94</option>
		<option value="95">95</option>
		<option value="96">96</option>
		<option value="97">97</option>
		<option value="98">98</option>
		<option value="99">99</option>
		<option value="100">100</option>
	</select>

	{cloud_selector_ccu_per_hour}

	<input type="text" name="product_name" class="product_name" id="product_name" maxlength="20" size="20" value="[{cloud_selector_product_name}]"/>
	<input type="text" name="product_description" class="product_description" id="product_description" maxlength="200" size="20" value="[{cloud_selector_product_description}]"/>

	<input type="hidden" name="product_type" value="ha">
	<input type="hidden" name="cloud_selector" value="add">
	<input type="submit" value="Add" name="Add">

	</nobr>
</div>

</div>


</form>
<hr>
{table}



