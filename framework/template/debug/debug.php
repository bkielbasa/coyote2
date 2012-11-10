<html>
<head>

<script type="text/javascript">
//<![CDATA[
function $$(objId)
{
	return document.getElementById(objId);
}

function switchDebug(windowId)
{
	if ($$('debug_' + windowId).style.display == 'none')
	{
		$$('debug_' + windowId).style.display = 'block';
		$$('d_' + windowId).className = 'selected';
	}
	else
	{
		$$('debug_' + windowId).style.display = 'none';
		$$('d_' + windowId).className = '';
	}
}

function showTree(treeId)
{
	if ($$(treeId).style.display == 'block')
	{
		$$(treeId).style.display = 'none';
		$$('i_' + treeId).className = '';
	}
	else
	{
		$$(treeId).style.display = 'block';
		$$('i_' + treeId).className = 'open';
	}
}
//]]>
</script>
<style type="text/css">
<!--
#debug
{
	position:			fixed;
	z-index:			1000;
	top:				0;
	right:				0;
	background:			#f6f3f0 url("data:image/png,%89PNG%0D%0A%1A%0A%00%00%00%0DIHDR%00%00%00%16%00%00%00%16%08%06%00%00%00%C4%B4l%3B%00%00%00%09pHYs%00%00.%23%00%00.%23%01x%A5%3Fv%00%00%0AOiCCPPhotoshop%20ICC%20profile%00%00x%DA%9DSgTS%E9%16%3D%F7%DE%F4BK%88%80%94KoR%15%08%20RB%8B%80%14%91%26*!%09%10J%88!%A1%D9%15Q%C1%11EE%04%1B%C8%A0%88%03%8E%8E%80%8C%15Q%2C%0C%8A%0A%D8%07%E4!%A2%8E%83%A3%88%8A%CA%FB%E1%7B%A3k%D6%BC%F7%E6%CD%FE%B5%D7%3E%E7%AC%F3%9D%B3%CF%07%C0%08%0C%96H3Q5%80%0C%A9B%1E%11%E0%83%C7%C4%C6%E1%E4.%40%81%0A%24p%00%10%08%B3d!s%FD%23%01%00%F8~%3C%3C%2B%22%C0%07%BE%00%01x%D3%0B%08%00%C0M%9B%C00%1C%87%FF%0F%EAB%99%5C%01%80%84%01%C0t%918K%08%80%14%00%40z%8EB%A6%00%40F%01%80%9D%98%26S%00%A0%04%00%60%CBcb%E3%00P-%00%60'%7F%E6%D3%00%80%9D%F8%99%7B%01%00%5B%94!%15%01%A0%91%00%20%13e%88D%00h%3B%00%AC%CFV%8AE%00X0%00%14fK%C49%00%D8-%000IWfH%00%B0%B7%00%C0%CE%10%0B%B2%00%08%0C%000Q%88%85)%00%04%7B%00%60%C8%23%23x%00%84%99%00%14F%F2W%3C%F1%2B%AE%10%E7*%00%00x%99%B2%3C%B9%249E%81%5B%08-q%07WW.%1E(%CEI%17%2B%146a%02a%9A%40.%C2y%99%192%814%0F%E0%F3%CC%00%00%A0%91%15%11%E0%83%F3%FDx%CE%0E%AE%CE%CE6%8E%B6%0E_-%EA%BF%06%FF%22bb%E3%FE%E5%CF%ABp%40%00%00%E1t~%D1%FE%2C%2F%B3%1A%80%3B%06%80m%FE%A2%25%EE%04h%5E%0B%A0u%F7%8Bf%B2%0F%40%B5%00%A0%E9%DAW%F3p%F8~%3C%3CE%A1%90%B9%D9%D9%E5%E4%E4%D8J%C4B%5Ba%CAW%7D%FEg%C2_%C0W%FDl%F9~%3C%FC%F7%F5%E0%BE%E2%24%812%5D%81G%04%F8%E0%C2%CC%F4L%A5%1C%CF%92%09%84b%DC%E6%8FG%FC%B7%0B%FF%FC%1D%D3%22%C4Ib%B9X*%14%E3Q%12q%8ED%9A%8C%F32%A5%22%89B%92)%C5%25%D2%FFd%E2%DF%2C%FB%03%3E%DF5%00%B0j%3E%01%7B%91-%A8%5Dc%03%F6K'%10Xt%C0%E2%F7%00%00%F2%BBo%C1%D4(%08%03%80h%83%E1%CFw%FF%EF%3F%FDG%A0%25%00%80fI%92q%00%00%5ED%24.T%CA%B3%3F%C7%08%00%00D%A0%81*%B0A%1B%F4%C1%18%2C%C0%06%1C%C1%05%DC%C1%0B%FC%606%84B%24%C4%C2B%10B%0Ad%80%1Cr%60)%AC%82B(%86%CD%B0%1D*%60%2F%D4%40%1D4%C0Qh%86%93p%0E.%C2U%B8%0E%3Dp%0F%FAa%08%9E%C1(%BC%81%09%04A%C8%08%13a!%DA%88%01b%8AX%23%8E%08%17%99%85%F8!%C1H%04%12%8B%24%20%C9%88%14Q%22K%915H1R%8AT%20UH%1D%F2%3Dr%029%87%5CF%BA%91%3B%C8%002%82%FC%86%BCG1%94%81%B2Q%3D%D4%0C%B5C%B9%A87%1A%84F%A2%0B%D0dt1%9A%8F%16%A0%9B%D0r%B4%1A%3D%8C6%A1%E7%D0%ABh%0F%DA%8F%3EC%C70%C0%E8%18%073%C4l0.%C6%C3B%B18%2C%09%93c%CB%B1%22%AC%0C%AB%C6%1A%B0V%AC%03%BB%89%F5c%CF%B1w%04%12%81E%C0%096%04wB%20a%1EAHXLXN%D8H%A8%20%1C%244%11%DA%097%09%03%84Q%C2'%22%93%A8K%B4%26%BA%11%F9%C4%18b21%87XH%2C%23%D6%12%8F%13%2F%10%7B%88C%C47%24%12%89C2'%B9%90%02I%B1%A4T%D2%12%D2F%D2nR%23%E9%2C%A9%9B4H%1A%23%93%C9%DAdk%B2%079%94%2C%20%2B%C8%85%E4%9D%E4%C3%E43%E4%1B%E4!%F2%5B%0A%9Db%40q%A4%F8S%E2(R%CAjJ%19%E5%10%E54%E5%06e%982AU%A3%9AR%DD%A8%A1T%115%8FZB%AD%A1%B6R%AFQ%87%A8%134u%9A9%CD%83%16IK%A5%AD%A2%95%D3%1Ah%17h%F7i%AF%E8t%BA%11%DD%95%1EN%97%D0W%D2%CB%E9G%E8%97%E8%03%F4w%0C%0D%86%15%83%C7%88g(%19%9B%18%07%18g%19w%18%AF%98L%A6%19%D3%8B%19%C7T071%EB%98%E7%99%0F%99oUX*%B6*%7C%15%91%CA%0A%95J%95%26%95%1B*%2FT%A9%AA%A6%AA%DE%AA%0BU%F3U%CBT%8F%A9%5ES%7D%AEFU3S%E3%A9%09%D4%96%ABU%AA%9DP%EBS%1BSg%A9%3B%A8%87%AAg%A8oT%3F%A4~Y%FD%89%06Y%C3L%C3OC%A4Q%A0%B1_%E3%BC%C6%20%0Bc%19%B3x%2C!k%0D%AB%86u%815%C4%26%B1%CD%D9%7Cv*%BB%98%FD%1D%BB%8B%3D%AA%A9%A19C3J3W%B3R%F3%94f%3F%07%E3%98q%F8%9CtN%09%E7(%A7%97%F3~%8A%DE%14%EF)%E2)%1B%A64L%B91e%5Ck%AA%96%97%96X%ABH%ABQ%ABG%EB%BD6%AE%ED%A7%9D%A6%BDE%BBY%FB%81%0EA%C7J'%5C'Gg%8F%CE%05%9D%E7S%D9S%DD%A7%0A%A7%16M%3D%3A%F5%AE.%AAk%A5%1B%A1%BBDw%BFn%A7%EE%98%9E%BE%5E%80%9ELo%A7%DEy%BD%E7%FA%1C%7D%2F%FDT%FDm%FA%A7%F5G%0CX%06%B3%0C%24%06%DB%0C%CE%18%3C%C55qo%3C%1D%2F%C7%DB%F1QC%5D%C3%40C%A5a%95a%97%E1%84%91%B9%D1%3C%A3%D5F%8DF%0F%8Ci%C6%5C%E3%24%E3m%C6m%C6%A3%26%06%26!%26KM%EAM%EE%9ARM%B9%A6)%A6%3BL%3BL%C7%CD%CC%CD%A2%CD%D6%995%9B%3D1%D72%E7%9B%E7%9B%D7%9B%DF%B7%60ZxZ%2C%B6%A8%B6%B8eI%B2%E4Z%A6Y%EE%B6%BCn%85Z9Y%A5XUZ%5D%B3F%AD%9D%AD%25%D6%BB%AD%BB%A7%11%A7%B9N%93N%AB%9E%D6g%C3%B0%F1%B6%C9%B6%A9%B7%19%B0%E5%D8%06%DB%AE%B6m%B6%7Dagb%17g%B7%C5%AE%C3%EE%93%BD%93%7D%BA%7D%8D%FD%3D%07%0D%87%D9%0E%AB%1DZ%1D~s%B4r%14%3AV%3A%DE%9A%CE%9C%EE%3F%7D%C5%F4%96%E9%2FgX%CF%10%CF%D83%E3%B6%13%CB)%C4i%9DS%9B%D3Gg%17g%B9s%83%F3%88%8B%89K%82%CB.%97%3E.%9B%1B%C6%DD%C8%BD%E4Jt%F5q%5D%E1z%D2%F5%9D%9B%B3%9B%C2%ED%A8%DB%AF%EE6%EEi%EE%87%DC%9F%CC4%9F)%9EY3s%D0%C3%C8C%E0Q%E5%D1%3F%0B%9F%950k%DF%AC~OCO%81g%B5%E7%23%2Fc%2F%91W%AD%D7%B0%B7%A5w%AA%F7a%EF%17%3E%F6%3Er%9F%E3%3E%E3%3C7%DE2%DEY_%CC7%C0%B7%C8%B7%CBO%C3o%9E_%85%DFC%7F%23%FFd%FFz%FF%D1%00%A7%80%25%01g%03%89%81A%81%5B%02%FB%F8z%7C!%BF%8E%3F%3A%DBe%F6%B2%D9%EDA%8C%A0%B9A%15A%8F%82%AD%82%E5%C1%AD!h%C8%EC%90%AD!%F7%E7%98%CE%91%CEi%0E%85P~%E8%D6%D0%07a%E6a%8B%C3~%0C'%85%87%85W%86%3F%8Ep%88X%1A%D11%975w%D1%DCCs%DFD%FAD%96D%DE%9Bg1O9%AF-J5*%3E%AA.j%3C%DA7%BA4%BA%3F%C6.fY%CC%D5X%9DXIlK%1C9.*%AE6nl%BE%DF%FC%ED%F3%87%E2%9D%E2%0B%E3%7B%17%98%2F%C8%5Dpy%A1%CE%C2%F4%85%A7%16%A9.%12%2C%3A%96%40L%88N8%94%F0A%10*%A8%16%8C%25%F2%13w%25%8E%0Ay%C2%1D%C2g%22%2F%D16%D1%88%D8C%5C*%1EN%F2H*Mz%92%EC%91%BC5y%24%C53%A5%2C%E5%B9%84'%A9%90%BCL%0DL%DD%9B%3A%9E%16%9Av%20m2%3D%3A%BD1%83%92%91%90qB%AA!M%93%B6g%EAg%E6fv%CB%ACe%85%B2%FE%C5n%8B%B7%2F%1E%95%07%C9k%B3%90%AC%05Y-%0A%B6B%A6%E8TZ(%D7*%07%B2geWf%BF%CD%89%CA9%96%AB%9E%2B%CD%ED%CC%B3%CA%DB%907%9C%EF%9F%FF%ED%12%C2%12%E1%92%B6%A5%86KW-%1DX%E6%BD%ACj9%B2%3Cqy%DB%0A%E3%15%05%2B%86V%06%AC%3C%B8%8A%B6*m%D5O%AB%EDW%97%AE~%BD%26zMk%81%5E%C1%CA%82%C1%B5%01k%EB%0BU%0A%E5%85%7D%EB%DC%D7%ED%5DOX%2FY%DF%B5a%FA%86%9D%1B%3E%15%89%8A%AE%14%DB%17%97%15%7F%D8(%DCx%E5%1B%87o%CA%BF%99%DC%94%B4%A9%AB%C4%B9d%CFf%D2f%E9%E6%DE-%9E%5B%0E%96%AA%97%E6%97%0En%0D%D9%DA%B4%0D%DFV%B4%ED%F5%F6E%DB%2F%97%CD(%DB%BB%83%B6C%B9%A3%BF%3C%B8%BCe%A7%C9%CE%CD%3B%3FT%A4T%F4T%FAT6%EE%D2%DD%B5a%D7%F8n%D1%EE%1B%7B%BC%F64%EC%D5%DB%5B%BC%F7%FD%3E%C9%BE%DBU%01UM%D5f%D5e%FBI%FB%B3%F7%3F%AE%89%AA%E9%F8%96%FBm%5D%ADNmq%ED%C7%03%D2%03%FD%07%23%0E%B6%D7%B9%D4%D5%1D%D2%3DTR%8F%D6%2B%EBG%0E%C7%1F%BE%FE%9D%EFw-%0D6%0DU%8D%9C%C6%E2%23pDy%E4%E9%F7%09%DF%F7%1E%0D%3A%DAv%8C%7B%AC%E1%07%D3%1Fv%1Dg%1D%2FjB%9A%F2%9AF%9BS%9A%FB%5Bb%5B%BAO%CC%3E%D1%D6%EA%DEz%FCG%DB%1F%0F%9C4%3CYyJ%F3T%C9i%DA%E9%82%D3%93g%F2%CF%8C%9D%95%9D%7D~.%F9%DC%60%DB%A2%B6%7B%E7c%CE%DFj%0Fo%EF%BA%10t%E1%D2E%FF%8B%E7%3B%BC%3B%CE%5C%F2%B8t%F2%B2%DB%E5%13W%B8W%9A%AF%3A_m%EAt%EA%3C%FE%93%D3O%C7%BB%9C%BB%9A%AE%B9%5Ck%B9%EEz%BD%B5%7Bf%F7%E9%1B%9E7%CE%DD%F4%BDy%F1%16%FF%D6%D5%9E9%3D%DD%BD%F3zo%F7%C5%F7%F5%DF%16%DD~r'%FD%CE%CB%BB%D9w'%EE%AD%BCO%BC_%F4%40%EDA%D9C%DD%87%D5%3F%5B%FE%DC%D8%EF%DC%7Fj%C0w%A0%F3%D1%DCG%F7%06%85%83%CF%FE%91%F5%8F%0FC%05%8F%99%8F%CB%86%0D%86%EB%9E8%3E99%E2%3Fr%FD%E9%FC%A7C%CFd%CF%26%9E%17%FE%A2%FE%CB%AE%17%16%2F~%F8%D5%EB%D7%CE%D1%98%D1%A1%97%F2%97%93%BFm%7C%A5%FD%EA%C0%EB%19%AF%DB%C6%C2%C6%1E%BE%C9x31%5E%F4V%FB%ED%C1w%DCw%1D%EF%A3%DF%0FO%E4%7C%20%7F(%FFh%F9%B1%F5S%D0%A7%FB%93%19%93%93%FF%04%03%98%F3%FCc3-%DB%00%00%00%04gAMA%00%00%B1%8E%7C%FBQ%93%00%00%00%20cHRM%00%00z%25%00%00%80%83%00%00%F9%FF%00%00%80%E9%00%00u0%00%00%EA%60%00%00%3A%98%00%00%17o%92_%C5F%00%00%04%25IDATx%DA%8C%95%5DlTU%10%80%BF9%F7%EE%DD%DDv%0B%A5P%11R%8A%04%8A%91%BF%A0%82%06)%8A%3F%A0%82%FA%A0%18Ml%E2%8B%C1%A8%90%80%18%94%84%07%95%F8P%FCI%8C%C6D%C4D%821%0A%0A%E2%83%1A%03%8A%7F%98%12i%25%14%0C!j%2Cj%EA%96B%D9%EEvw%EF%DE%7B%C6%07Ne%83%15%9D%E4%24g%CE%9D%F92sg%E6%1Cy%E7%DD%F7%18I%C4%08%85%7C%81%BEl%1F%08%0B%D5%EArk%ED%5D%C6%98F%E00%F05%F0%91%DB%FFC%7C%FEE%D4%EA%C4L%26%B3(%95J%CD%89%E38%04%3A%06s%F9%0F%06N%17%E6%19%A3k0%F1%B3%C0%E3%C0%0E%E09%A0%A7%DA%DF%8C%C0%9C%0Dl%07%8E%5Bk_%F6%3Coz2%19%1CM%05%0D%C7%1B%C75%1D%AA%BBd%E0u%82%81%05%B6%5C%FF%12*%F5%C0J%60%2Fp%C3%C5%22~%14%D8%044%00_%01kU%E9%24%AE!L%1D%24L%7FG0%F13%BC%A1%09%03%E5%3F%16%AE%0B%B3s%7B%D5%26%DAA%5B%80%9D%C0%DD%C07%17%82%D7%01%9B%5D%16o%83%3C%22%B6%26%AF%A6H%98%3A%40%A1%E1%05T%C2%E5%12%8D%BA%DD%24%CF%92%99%B1s%F7%C0%D9)%CFG%F9%09%CD%E2%85%AB%80F%E0M%E0z%E0%CFa%F0m%40%BB%83~%09%B2R%A5%5C%8C%D2G(f%DE'J%FC%8C%A8%EF%89%26%DBAg%A2%06%5BNM%AD%99%B6k_%AEs%CD%06%BC%F0F%60%260%1D%D8%08%AC6%C0h%E0i%C0%03%0A%C0Z4(%AA)08v%13%B1%FF%2B%A2%01%E0%C5%80%9E%AF%AE%A4%11%0D%10%9Bw%99%0E%CB%03%C0%0C%1FX%0A%5C%EB%0E%3FD%13%5D%98%22%85%FAW%C0%A6%01o60%0B%A8%03%9A%80%10%B0j%FD%13~%DD%C9Jj%D2~J'%17%EF%11%BF%D8%034%03c%80%3B%7C%60%89%83%C6%82%D9e%CD%10%F91%EDD%C1%8F%88%ADM%02%DB%80%2B%9D%CD1%60%03%10%01%871%155%89%02%60r%C0%01%07%06h%F5%81%B9N9%83z'%E2%E0'*%C9%23%18%3B%0A%D0%10HU%A5y%DC%0D%05%221q%E1R%CA%BDW%23%5EI%81%EF%81%FB%9D%5D%8B%0F%5C%E6%94A%90SC%A3%B6blj*%E8dW%E9%86*%F04%E0%16%C0%AAz%DD%5E%FAT6%18%D7M%B1g%09%E2%0F%FD%5EeW%EF%9F%1F%12%A3%A2A%08%26%83%C8v%94%F9%AE%A0r%C1%F0%7C%0AX%60%2BbWab%3B%D2%E4%1A%E07%B7%CF%00%F5%20%19%90%B9%AE%C7e%04%1F%0FH%B8%F6JT%9D7V%ED%07%0D%F0%83S%1ATJSTJE%D4%86%FC%B7%84%A0%8AX%5C%00%F3%AA%BE%FD%EC%03%9F%03%0FB%ECCr%85hfo%EC%F5%AE7%B1w3%98%E1%DEU%A0%16X%06%04%7F%BB%5B%1F%8D%D2%80%D6%02%D7U%81%0F%F8%C0'%407%C8%2C%A8%DC%979%FD%E4%8B%A5%BA%1D%5B%C2T%C7%16kr%18%5B%E3%B2%07%E0(0%E3%DC%B5%1A%11%E5'Q%EAY%8C%24%8AK%5Ca%5D%13%F0%B1%01%FA%DC%B5%87JTolC%7B%E6%CCz2g%9E%A0%EE%F4F%BCh2*!*E%0FH%BA%DB%1A%F1*%01%10%A3%9E%0F%3CU%15%EDn%A0%CB%BBg%C5%BD%9C%8B%98%00X%04%F6%0A%95b%C6%8B%9A%BE%F0*%CD6(-%C2%AF4c%FD~%B5%5E6-%F8c%C5%0B%B3%1Af%B6%15%8E%B5%1D%B4Qz%A3%18%DB%E6%A0%BD%E7~%2B%FD%C3%60%80%FD%AE%08%AD%40%2B%12%B5%20%95%83%40%CE%8B%9AI%17%EED%BD%FEo%A3%DA%EE%B7%A8%8C~%E3l%D7c%1Dqq%7C%9Bx%95%CD%AE%83%F2%0E%DA%01P%0DV%07%1F%1E%CD%5B%816%11i%12%B1%25%F1T%93%A5kr%B9%93-%D1%E0%2F%F3E%8B%E3%D7%89_z%D5e%DA%0F%3C%04%EC%B9%D8%D3%B4%D7%AD%AB%14%5Djc%DB%A2%AA%CBT%2B%87%F2%F9B%ED%60%DF%E8%CB%8D%D1%D5%92%18%BA%C9%D9%EF%01%9E%01%BA%FE%D7%9B%07t%0A%D2%99%CDf)%97%CB%0BT%B5%D5%AA%7D%D8%F82%C7%15%FC5%07%DD%07%C4%17%3A%FF5%00%8B%03%A3%F4%C4I.%A6%00%00%00%00IEND%AEB%60%82") no-repeat 0 50%;
	border:				1px dotted #e5e2de;
	font-family:		Arial;
	font-size:			12px;
	padding:			5px 5px 5px 30px;
	filter:				alpha(opacity=70);
	opacity:			0.7;
	-moz-opacity:		0.7;
}

#debug ul
{
	list-style:			none;
	margin:				0;
	padding:			0;
}

#debug li
{
	float:				left;
	background:			url("data:image/gif,GIF89a%06%00%09%00%80%00%00%85%C2%26%FF%FF%FF!%F9%04%01%00%00%01%00%2C%00%00%00%00%06%00%09%00%00%02%0C%0C%82%06%97%9C%E8Tts%BDX%00%00%3B") no-repeat 0 50%;
	padding-left:		10px;
	margin-right:		20px;
	cursor:				pointer;
}

#debug li.close
{
	background:			transparent url("data:image/gif,GIF89a%14%00%14%00%E6%00%00%FF%FC%FB%FF%F8%F6%FE%F8%F6%FF%F2%EF%FF%EB%E7%FF%E3%DC%FD%E2%DC%FE%E2%DC%F9%E2%DC%FA%D7%CF%F8%CB%C0%F4%CB%C0%FD%B0%9D%F9%B0%9D%F8%AF%9D%F5%AF%9D%F1%AE%9D%F4%AE%9D%F2%AE%9D%F0%AE%9D%EE%AD%9D%EF%AD%9D%FF%9A%80%F0%9D%88%EC%9C%88%ED%8Aq%E9%89q%FFzY%F4xY%FFgA%E0nQ%DE%60A%DD%60A%F8Y1%E9V1%E2U1%F3K!%E9I!%FDA%11%FA%40%11%FC%40%11%F8%40%11%F6%3F%11%F3%3E%11%E3%3B%11%FD4%01%E0%3B%11%FC4%01%FA3%01%DB9%11%F52%01%F11%01%F21%01%EE1%01%EB0%01%E5%2F%01%E3%2F%01%DE.%01%E1.%01%DE-%01%DB-%01%D8%2C%01%D6%2C%01%D2%2B%01%D3%2B%01%FF%FF%FF%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00!%F9%04%05%14%00A%00%2C%00%00%00%00%14%00%14%00%00%07%80%80A%82%83%84%85%86%87%88%89%8A%88%16%1D%01%8B%86%26-(%8F%90%83%0500'%05%86%05%1B%88%03*22%07%84%0C%2F%9C%88%01%2433%06A%01!)%04%90%1C55%0D4%2B%95%90%0E6%BF%02%96A%02%257%C6%0F%96%0A88%22%09%2C%3A%11%8B%19%3B%3B%17%82%02.9%12%88%021%3D%3D%13%84%00%23%3C%10%87%1F%40%40%15%87%18%3E%1A%86%0B%20%08%89%14%3F%1F%C2%84%08%1E%F8%FC%FD%C2%81%00%3B") no-repeat 100% 50%;
}

#debug li a
{
	color:				black;
	text-decoration:	none;
}

#debug .selected
{
	border-bottom:		1px dotted #e5e2de;
	font-weight:		bold;
}

.debug
{
	position:			absolute;
	top:				0;
	left:				0;
	width:				100%;
	height:				auto;
	margin:				0 auto 0 auto;
	display:			none;
	background:			#f6f3f0;
	border:				1px dotted black;
	padding:			30px 10px 10px 10px;
	font-family:		Arial;
	font-size:			11px;
	z-index:			999;
}

.debug fieldset
{
	border:				none;
	padding:			2px;
	margin-top:			10px;
}

.debug fieldset div
{
	padding:			5px;
}

.debug fieldset b
{
	display:			block;
	width:				20%;
	float:				left;
}

.debug h1
{
	border:				none;
	font-size:			1.5em;
}

.debug ul
{
	list-style:			none;
}

.debug ul li
{
	padding:			2px 0 2px 0;
}

.debug ul li em
{
	background:			url("data:image/gif,GIF89a%09%00%09%00%B3%00%00%00%00%00%84%84%84%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF!%F9%04%01%00%00%02%00%2C%00%00%00%00%09%00%09%00%00%04%170%C8I%85%BDB%5Ep%B5%E5%96%06%8Cc%16l%DD%F9%A5XJQ%11%00%3B") no-repeat 0 50%;
	width:				9px;
	height:				9px;
	padding:			5px;
}

.debug ul li em.open
{
	background:			url("data:image/gif,GIF89a%09%00%09%00%B3%00%00%00%00%00%84%84%84%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF%FF!%F9%04%01%00%00%02%00%2C%00%00%00%00%09%00%09%00%00%04%150%C8I%85%BDB%E2%ABw%0E%02%20%8A%9FWz%DDF%ADA%04%00%3B") no-repeat 0 50%;
}

#logs, #sql
{
	font-size:			10px;
	width:				90%;
	padding:			5px;
	margin:				0 auto 0 auto;
}

#logs #id,
#sql #id
{
	width:				5%;
}

#logs #type
{
	width:				10%;
}

#logs #time,
#sql #time
{
	width:				10%;
}

#logs #message,
#sql #message
{
	width:				75%;
}

#logs td,
#sql td
{
	padding:			5px;
}

#logs thead td,
#sql thead td
{
	background-color:	black;
	color:				white;
}
-->
</style>
</head>
<body>
<div id="debug">
	<ul>
		<li id="d_1"><a onclick="switchDebug(1);"><?= $core->version(); ?></a></li>
		<li id="d_2"><a onclick="switchDebug(2);"><?= $memory_usage; ?> MB</a></li>
		<li id="d_3"><a onclick="switchDebug(3);">Logs</a></li>
		<li id="d_4"><a onclick="switchDebug(4);">Vars</a></li>
		<?php if ($reflection) : ?>
		<li id="d_5"><a onclick="switchDebug(5);">Reflection</a></li>
		<?php endif; ?>
		<?php if (isset($sql)) : ?>
		<li id="d_6"><a onclick="switchDebug(6);">SQL (<?= count($sql); ?>)</a></li>
		<?php endif; ?>
		<li id="d_7"><a onclick="switchDebug(7);"><?= $estimated; ?> sec.</a></li>
		<li class="close" title="Schowaj pasek" onclick="$$('debug').style.display = 'none';">&nbsp;</li>
	</ul>

</div>

<div style="display: none" class="debug" id="debug_1">
	<h1>Coyote <?= $core->version(); ?></h1>

	<p>Copyright &copy; 2003-<?= date('Y'); ?> Adam Boduch, Coyote Group</p>

	<fieldset>
		<div><b>PHP version:</b> <?= phpversion(); ?></div>
		<div><b>Zend Engine:</b> <?= zend_version(); ?></div>
		<div><b>Current user:</b> <?= get_current_user(); ?></div>
		<div><b>Os:</b> <?= php_uname(); ?></div>
	</fieldset>
	<hr />
	Zamknięcie tego paska przyspieszy generowanie strony. Jeżeli chcesz wyłączyć ten pasek,
	zmień wartość stałej <b>DEBUG</b> w pliku <em>/index.php</em> na <b>FALSE</b>.
</div>

<div style="display: none" class="debug" id="debug_2">
	<h1>Memory usage</h1>

	<fieldset>
		<div><b>Real usage:</b> <?= $memory_usage_real; ?> MB</div>
		<div><b>Memory limit:</b> <?= ini_get('memory_limit'); ?></div>
		<div><b>Peak usage:</b> <?= $memory_peak; ?> MB</div>
	</fieldset>
</div>

<div style="display: none" class="debug" id="debug_3">
	<h1>Logs</h1>

	<table id="logs">
		<thead>
			<tr>
				<td id="id">ID</td>
				<td id="type">Type</td>
				<td id="time">Time</td>
				<td id="message">Message</td>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($message as $row) : ?>
		<tr>
			<td><?= $row['id']; ?></td>
			<td><?= $row['type']; ?></td>
			<td><?= $row['time']; ?> sec.</td>
			<td><?= $row['message']; ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

<div style="display: none" class="debug" id="debug_4">
	<h1>Vars & configs</h1>

	<ul>
		<li style="cursor: pointer;" onclick="showTree('constant'); return;"><em id="i_constant"></em> Consts</li>
		<fieldset id="constant" style="display: none">
			<?php foreach ($constant as $const => $value) : ?>
			<div><b><?= $const; ?></b> <?= $value; ?></div>
			<?php endforeach; ?>
		</fieldset>
		<li style="cursor: pointer;" onclick="showTree('get'); return;"><em id="i_get"></em> $_GET</li>
		<fieldset id="get" style="display: none">
			<?php foreach ($_GET as $key => $value) : ?>
			<div><b><?= $key; ?></b> <?= var_dump($_GET[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>
		<li style="cursor: pointer;" onclick="showTree('post'); return;"><em id="i_post"></em> $_POST</li>
		<fieldset id="post" style="display: none">
			<?php foreach ($_POST as $key => $value) : ?>
			<div><b><?= $key; ?></b> <?= var_dump($_POST[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>
		<li style="cursor: pointer;" onclick="showTree('cookie'); return;"><em id="i_cookie"></em> $_COOKIE</li>
		<fieldset id="cookie" style="display: none">
			<?php foreach ($_COOKIE as $key => $value) : ?>
			<div><b><?= $key; ?></b> <?= var_dump($_COOKIE[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>
		<li style="cursor: pointer;" onclick="showTree('server'); return;"><em id="i_server"></em> $_SERVER</li>
		<fieldset id="server" style="display: none">
			<?php foreach ($_SERVER as $key => $value) : ?>
			<div><b><?= $key; ?></b> <?= var_dump($_SERVER[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>
		<li style="cursor: pointer;" onclick="showTree('session'); return;"><em id="i_session"></em> $_SESSION</li>
		<fieldset id="server" style="display: none">
			<?php foreach ($_SESSION as $key => $value) : ?>
			<div><b><?= $key; ?></b> <?= var_dump($_SESSION[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>

		<li style="cursor: pointer;" onclick="showTree('config'); return;"><em id="i_config"></em> Project config</li>
		<fieldset id="config" style="display: none">
			<?php foreach ($config as $key => $value) : ?>
			<div style="clear: both"><b><?= $key; ?></b> <?= var_dump($config[$key]); ?></div>
			<?php endforeach; ?>
		</fieldset>
	</ul>
</div>

<?php if ($reflection) : ?>
<div style="display: none" class="debug" id="debug_5">
	<h1>Reflection</h1>

	<ul>
		<li style="cursor: pointer;" onclick="showTree('class'); return;"><em id="i_class"></em> Classes</li>
		<fieldset id="class" style="display: none">
			<?php foreach ($reflection as $id => $row) : ?>

				<li style="cursor: pointer;" onclick="showTree('class_<?= $row['name']; ?>'); return;"><em id="i_class_<?= $row['name']; ?>"></em> <?= $row['name']; ?></li>
				<fieldset id="class_<?= $row['name']; ?>" style="display: none">
					<code><?= nl2br($row['comment']); ?></code>

					<?php foreach ($row['properties'] as $key => $property) : ?>
					<div>
						<?= highlight_code($property); ?>
					</div>
					<?php endforeach; ?>

					<?php foreach ($row['methods'] as $key => $method) : ?>
					<div>
						<?= highlight_code($method, 'function'); ?>
					</div>
					<?php endforeach; ?>
				</fieldset>

			<?php endforeach; ?>
		</fieldset>
	</ul>
</div>
<?php endif; ?>

<?php if (isset($sql)) : ?>
<div style="display: none" class="debug" id="debug_6">
	<h1>SQL</h1>

	<table id="sql">
		<thead>
			<tr>
				<td id="id">ID</td>
				<td id="time">Time</td>
				<td>SQL</td>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($sql as $row) : ?>
		<tr>
			<td><?= $row['id']; ?></td>
			<td><?= $row['time']; ?> sec.</td>
			<td><?= $row['message']; ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endif; ?>

<div style="display: none" class="debug" id="debug_7">
	<h1>Benchmark</h1>

	<fieldset>
		<?php if (isset($sql)) : ?>
		<div><b>Czas wykonywania instrukcji SQL:</b> <?= $totalSqlTime; ?> sec. (<?= number_format(($totalSqlTime * 100) / $estimated); ?>%)</div>
		<?php endif; ?>
		<div><b>Czas generowania kodu PHP:</b> <?= $estimated - $totalSqlTime; ?> sec. (<?= number_format((($estimated - $totalSqlTime) * 100) / $estimated); ?>%)</div>
		<div><b>Czas generowania raportu:</b> <?= benchmark::elapsed() - $estimated; ?> sec.</div>
		<div><hr /></div>

		<div><b>W sumie:</b> <?= benchmark::elapsed(); ?> sek.</div>
	</fieldset>
</div>
</body>
</html>