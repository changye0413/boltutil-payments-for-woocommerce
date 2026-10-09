'use strict';
// Execute the shipped route helper and Blocks UI, not a duplicate selection implementation.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const combinations={};for(const token of ['USDT','USDC'])for(const network of ['TRC20','ERC20','BEP20','POLYGON','SOLANA','BASE']){
 if(token==='USDC'&&network==='TRC20')continue;combinations[token+':'+network]={token,network,label:network,short:token};
}
const context={window:{}};vm.runInNewContext(fs.readFileSync('assets/routes.js','utf8'),context);
const routes=context.window.BoltUtilCheckoutRoutes;
assert.equal(routes.forToken(combinations,'USDT').length,6);assert.equal(routes.forToken(combinations,'USDC').length,5);
assert.equal(routes.select(combinations,'USDC','USDT:TRC20'),'USDC:ERC20');
assert.equal(routes.select(combinations,'USDC','USDT:BASE'),'USDC:BASE');
assert.equal(routes.select({},'USDC'),'');
let registered,states=[],cursor=0,effects=[],setup,cleanup=0;
context.window.wp={element:{createElement:(type,props,...children)=>({type,props:props||{},children:children.flat()}),useState:initial=>{const i=cursor++;if(states[i]===undefined)states[i]=initial;return[states[i],value=>states[i]=value];},useEffect:fn=>effects.push(fn)},htmlEntities:{decodeEntities:s=>s}};
context.window.wc={wcSettings:{getSetting:()=>({networks:combinations,tokenIcons:{}})},wcBlocksRegistry:{registerPaymentMethod:obj=>registered=obj}};
vm.runInNewContext(fs.readFileSync('assets/blocks.js','utf8'),context);
const props={eventRegistration:{onPaymentSetup:fn=>{setup=fn;return()=>cleanup++;}},emitResponse:{responseTypes:{SUCCESS:'success',ERROR:'error'}}};
let unsubscribe;
const render=()=>{cursor=0;effects=[];const tree=registered.content.type(props);if(unsubscribe)unsubscribe();effects.forEach(fn=>unsubscribe=fn());return tree;};
const nodes=(tree,predicate)=>[...(predicate(tree)?[tree]:[]),...(tree.children||[]).filter(c=>c&&typeof c==='object').flatMap(c=>nodes(c,predicate))];
let tree=render();assert.equal(nodes(tree,n=>n.props.name==='boltutil-block-network').length,6);
for(const token of ['USDC','USDT']){
 nodes(tree,n=>n.props.name==='boltutil-block-token'&&n.props.value===token)[0].props.onChange();tree=render();
 const inputs=nodes(tree,n=>n.props.name==='boltutil-block-network');assert.equal(inputs.length,token==='USDC'?5:6);
 for(const input of inputs){input.props.onChange();tree=render();assert.equal(setup().type,'success');assert.equal(setup().meta.paymentMethodData.boltutil_route,input.props.value);assert.ok(input.props.value.startsWith(token+':'));}
}
assert.ok(cleanup>0,'Old Blocks payment callbacks are unsubscribed after a coin/network change');
// Classic checkout survives WooCommerce replacing its payment DOM after address/shipping changes.
let panels=[],observer;
const makePanel=()=>{const coins=['USDT','USDC'].map(value=>({name:'boltutil_token',value,checked:false}));const inputs=Object.keys(combinations).map(value=>({name:'boltutil_route',value,dataset:combinations[value],checked:false,disabled:false,label:{hidden:false},closest(){return this.label;}}));return{dataset:{},coins,inputs,fieldset:{hidden:true},querySelectorAll:s=>s.includes('boltutil_token')?coins:inputs,querySelector(){return this.fieldset;},addEventListener(type,fn){this.change=fn;}};};
context.document={readyState:'complete',body:{},querySelectorAll:()=>panels};context.MutationObserver=class{constructor(fn){observer=fn;}observe(){}};
panels=[makePanel()];vm.runInNewContext(fs.readFileSync('assets/checkout.js','utf8'),context);
const current=()=>panels[0];current().change({target:current().coins[1]});
assert.equal(current().inputs.filter(i=>!i.disabled).length,5);assert.equal(current().inputs.find(i=>i.checked).value,'USDC:ERC20');
const base=current().inputs.find(i=>i.value==='USDC:BASE');base.checked=true;current().change({target:base});
panels=[makePanel()];observer();assert.equal(current().coins.find(i=>i.checked).value,'USDC');assert.equal(current().inputs.find(i=>i.checked).value,'USDC:BASE');
current().change({target:current().coins[0]});assert.equal(current().inputs.find(i=>i.checked).value,'USDT:BASE');
assert.ok(current().inputs.filter(i=>i.disabled).every(i=>i.label.hidden&&!i.checked));
console.log('Checkout fixtures passed: 11 route submissions, coin switching, Blocks callback cleanup and classic AJAX refresh.');
