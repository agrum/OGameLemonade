function parseReport() {
	var parserContent = document.getElementById('parser').value;
	var pattern;
	var newValue = '';
	var i;
	
	if(!langActivateTemplate(parserContent))
		return;
	
	for(i = 0; i < LangTemplateActive.langUnit.length; i++)
	{
		newValue = '';
		pattern = new RegExp(LangTemplateActive.langUnit[i].report + '[\\t ][0-9]+','m');
		
		if(pattern.test(parserContent))
		{
			newValue = pattern.exec(parserContent)[0];
			newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		}
		if(document.getElementById(LangTemplateActive.langUnit[i].short + "_D"))
			document.getElementById(LangTemplateActive.langUnit[i].short + "_D").value = newValue;
	}
	
	for(i = 0; i < LangTemplateActive.langTech.length; i++)
	{
		newValue = '';
		pattern = new RegExp(LangTemplateActive.langTech[i].report + '[\\t ][0-9]+','m');
		
		if(pattern.test(parserContent))
		{
			newValue = pattern.exec(parserContent)[0];
			newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		}
		if(document.getElementById(LangTemplateActive.langTech[i].short + "_D"))
			document.getElementById(LangTemplateActive.langTech[i].short + "_D").value = newValue;
	}
}

function cleanReport() {
	document.getElementById('parser').value = '';
}
