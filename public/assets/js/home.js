import{G as e}from"./grid-BQLpeedw.js";import"./_commonjsHelpers-Cpj98o6Y.js";class o{constructor(){this.tagContainer=document.getElementById("tagContainer"),this.leftArrow=document.querySelector(".scroll-arrow-left"),this.rightArrow=document.querySelector(".scroll-arrow-right"),this.boundUpdateArrows=this.updateArrows.bind(this),this.updateArrows(),this.tagContainer.addEventListener("scroll",this.boundUpdateArrows),window.addEventListener("resize",this.boundUpdateArrows),this.leftArrow.addEventListener("click",()=>this.scrollContainer(-1)),this.rightArrow.addEventListener("click",()=>this.scrollContainer(1))}updateArrows(){const t=this.tagContainer.scrollLeft,r=this.tagContainer.scrollWidth-this.tagContainer.clientWidth;t>0?this.leftArrow.classList.remove("d-none"):this.leftArrow.classList.add("d-none"),t<r-1?this.rightArrow.classList.remove("d-none"):this.rightArrow.classList.add("d-none")}scrollContainer(t){const r=this.tagContainer.clientWidth;this.tagContainer.scrollBy({left:t*r,behavior:"smooth"})}}document.addEventListener("DOMContentLoaded",()=>{new o,new e});
