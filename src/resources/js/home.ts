import { ScrollHandler } from "./components/scroll";
import { GridLayoutHandler } from "./components/grid";

document.addEventListener('DOMContentLoaded', (): void => {
    new ScrollHandler();
    new GridLayoutHandler();
});