const pluginCheck = () => {
	console.log(`Modernizr: ${Modernizr ? 'checked' : 'not found'}`);
	console.log(`Detectizr: ${Detectizr ? 'checked' : 'not found'}`);
};

const init = () => {
	console.log('I\'m Main module and I was initialized!');
};

export { init, pluginCheck };
