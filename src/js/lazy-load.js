
/**
 * 
 * get either the <div> or <img> where the image should be set
 * 
 * @param {DOMNode} el 
 * @returns {DOMNode} child
 */
const setChildElement = (el) => {
  const selector = !el.classList.contains('fl-module-photo') ?
    '.fl-row-content-wrap' :
    'img'
  return el.querySelector(selector)
}

/**
 * 
 * Set the attributes for the image on the child
 * 
 * @param {DOMNode} el 
 * @param {DOMNode} parent 
 * @returns {void}
 */
const setChildAtts = (el, parent) => {
  let imgSrc;
  if (el.localName === 'img' && el.complete) {
    imgSrc = el.getAttribute('data-img-src')
    const imgSrcSet = el.getAttribute('data-srcSet')
    el.setAttribute('src', imgSrc)
    el.setAttribute('srcset', imgSrcSet)
  } else {
    imgSrc = parent.getAttribute('data-img-src')
    el.style.backgroundImage = `url(${imgSrc})`
  }
  el.style.filter = 'blur(0)'
}

/**
 * 
 * Add a blur filter to images
 * Prepare images to transition to no blur
 * 
 * @param {DOMNode} el 
 */
const setChildStyle = (el) => {
    el.style.filter = 'blur(4px)'
    el.style.transition = 'filter 1s ease'
}

/**
 * 
 * Iterate through all entries and control when/how the image is set
 * Stop observing the target after the first intersection
 * 
 * @param {DOMNodeList} entries 
 * @param {Object} observer 
 */
const handleIntersect = (entries, observer) => {

  entries.forEach(entry => {

    const child = setChildElement(entry.target)

    setChildStyle(child)

    if (entry.intersectionRatio > 0) {
      setChildAtts(child, entry.target)

      // stop observing
      observer.unobserve(entry.target)
    }

  });
}

/**
 * 
 * Allow for lazy loading beaver builder images
 * 
 */
const lazyLoad = () => {

  const lazyStyleArr = [].slice.call(document.querySelectorAll('[data-lazy-loaded]'));

  // fallback for IE or no polyfill
  if (!window.IntersectionObserver) {
    lazyStyleArr.map(el => {
      const child = setChildElement(el)
      setChildAtts(child, child.parentNode)
    })
    return
  }

  const options = {
    root: null,
    rootMargin: '0px',
    threshold: 0
  }
  const observer = new IntersectionObserver(handleIntersect, options)

  lazyStyleArr.map(el => observer.observe(el))

}

export default lazyLoad