import { GridLayoutHandler} from "./components/grid";

document.addEventListener('DOMContentLoaded', (): void => {
    const container = document.getElementById('tagContainer');
    const rawTagId = container?.dataset.tagId;
    const tagId = rawTagId ? rawTagId : undefined;
    new GridLayoutHandler({ tagId });
});