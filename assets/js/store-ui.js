/* Translate only display messages emitted by the unchanged PAXIG customer script. */
document.addEventListener('DOMContentLoaded',()=>{
 const labels=Object.entries(window.paxigStoreLabels||{}).sort((a,b)=>b[0].length-a[0].length);
 const translate=text=>labels.reduce((value,[from,to])=>value.split(from).join(to),text);
 document.querySelectorAll('.paxig-customer-order [role="status"]').forEach(node=>{
  const update=()=>{const text=translate(node.textContent);if(text!==node.textContent)node.textContent=text;};
  update();new MutationObserver(update).observe(node,{childList:true,characterData:true,subtree:true});
 });
});
