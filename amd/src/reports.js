// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Registers events to opn/close reports results.
 *
 * @copyright  2023 L’Institut Agro Enseignement à distance
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const registerEventListeners = () => {
  const changeBtn = (button, collapsed) => {
    button.dataset.collapsed = !collapsed;
    button.setAttribute("aria-expanded", collapsed);
    button.querySelector("span").textContent =
      button.dataset[collapsed ? "buttonTextHide" : "buttonTextShow"];
    if (collapsed) {
      button.querySelector("i").classList.remove("fa-chevron-down");
      button.querySelector("i").classList.add("fa-chevron-up");
    } else {
      button.querySelector("i").classList.add("fa-chevron-down");
      button.querySelector("i").classList.remove("fa-chevron-up");
    }
  };

  const changeContent = (content, collapsed) => {
    if (
      (collapsed && content.classList.contains("open")) ||
      (!collapsed && !content.classList.contains("open"))
    ) {
      return;
    }
    content.classList[collapsed ? "add" : "remove"]("open");
    content.setAttribute("aria-expanded", collapsed);
    const height = content.querySelector("div").offsetHeight;
    const hidableParent = content.closest(
      ".hidable_content.hidable_content_higher_container"
    );
    content.style.height = collapsed ? height + "px" : 0;
    if (hidableParent && hidableParent !== content) {
      const parentCurrentHeight = Number(
        hidableParent.style.height.slice(0, -2)
      );
      if (collapsed) {
        hidableParent.style.height = parentCurrentHeight + height + "px";
      } else {
        hidableParent.style.height =
          (parentCurrentHeight - height < 0
            ? 0
            : parentCurrentHeight - height) + "px";
      }
    }
  };

  document.querySelectorAll(".show_hide_all_trigger").forEach((btn) => {
    const container = btn.closest(".hidable_content_higher_container");

    btn.addEventListener("click", () => {
      const collapsed = btn.dataset.collapsed === "true";
      if (btn.dataset.showAllChildren) {
        container
          .querySelectorAll(".show_hide_all_trigger")
          .forEach((b) => changeBtn(b, collapsed));
        container
          .querySelectorAll(".hidable_content")
          .forEach((c) => changeContent(c, collapsed));
      } else {
        changeBtn(btn, collapsed);
        changeContent(container.querySelector(".hidable_content"), collapsed);
      }
    });
  });
};

export const init = () => {
  registerEventListeners();
};
